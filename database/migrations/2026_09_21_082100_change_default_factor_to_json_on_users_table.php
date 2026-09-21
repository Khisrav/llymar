<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY default_factor VARCHAR(64) NOT NULL DEFAULT 'p3'");

        DB::table('users')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $raw = $user->default_factor;
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && $decoded !== []) {
                    continue;
                }

                $factor = is_string($raw) && $raw !== '' ? $raw : 'p3';
                DB::table('users')->where('id', $user->id)->update([
                    'default_factor' => json_encode([$factor]),
                ]);
            }
        });

        DB::statement("ALTER TABLE users MODIFY default_factor JSON NOT NULL DEFAULT (JSON_ARRAY('p3'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY default_factor VARCHAR(64) NOT NULL DEFAULT 'p3'");

        DB::table('users')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $decoded = json_decode($user->default_factor, true);
                $first = is_array($decoded) ? ($decoded[0] ?? 'p3') : ($user->default_factor ?: 'p3');
                if (!in_array($first, ['pz', 'p1', 'p2', 'p3', 'p4'], true)) {
                    $first = 'p3';
                }

                DB::table('users')->where('id', $user->id)->update([
                    'default_factor' => $first,
                ]);
            }
        });

        DB::statement("ALTER TABLE users MODIFY default_factor ENUM('pz', 'p1', 'p2', 'p3', 'p4') NOT NULL DEFAULT 'p3'");
    }
};
