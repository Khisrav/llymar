<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Finish ENUM/VARCHAR → JSON conversion when the earlier migration
     * failed mid-way (e.g. MySQL 5.7 rejecting JSON expression defaults).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'default_factor')) {
            return;
        }

        $column = collect(DB::select("SHOW COLUMNS FROM users LIKE 'default_factor'"))->first();
        $type = strtolower((string) ($column->Type ?? ''));

        // Already JSON (MySQL reports "json"; MariaDB often reports "longtext" + json_valid check).
        if (str_contains($type, 'json')) {
            return;
        }

        $create = DB::select('SHOW CREATE TABLE users')[0]->{'Create Table'} ?? '';
        if (str_contains(strtolower($create), 'json_valid(`default_factor`)')
            || str_contains(strtolower($create), 'json_valid(default_factor)')) {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY default_factor VARCHAR(64) NOT NULL DEFAULT 'p3'");

        DB::table('users')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $raw = $user->default_factor;
                $decoded = json_decode((string) $raw, true);
                if (is_array($decoded) && $decoded !== []) {
                    continue;
                }

                $factor = is_string($raw) && $raw !== '' ? $raw : 'p3';
                if (!in_array($factor, ['pz', 'p1', 'p2', 'p3', 'p4'], true)) {
                    $factor = 'p3';
                }

                DB::table('users')->where('id', $user->id)->update([
                    'default_factor' => json_encode([$factor]),
                ]);
            }
        });

        // MySQL 5.7: JSON NOT NULL is fine; non-NULL / expression defaults are not.
        DB::statement('ALTER TABLE users MODIFY default_factor JSON NOT NULL');
    }

    public function down(): void
    {
        // Irreversible companion to the JSON conversion; keep column as JSON.
    }
};
