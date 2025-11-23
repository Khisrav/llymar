<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Temporarily change users.default_factor to varchar to allow updating values
        DB::statement("ALTER TABLE users MODIFY default_factor VARCHAR(10) DEFAULT 'kz'");

        // Step 2: Update users table - migrate factor values from k* to p*
        DB::table('users')->update([
            'default_factor' => DB::raw("CASE default_factor
                WHEN 'kz' THEN 'pz'
                WHEN 'k1' THEN 'p1'
                WHEN 'k2' THEN 'p2'
                WHEN 'k3' THEN 'p3'
                WHEN 'k4' THEN 'p4'
                ELSE 'pz'
            END")
        ]);

        // Step 3: Update orders table - migrate selected_factor values from k* to p*
        DB::table('orders')->update([
            'selected_factor' => DB::raw("CASE selected_factor
                WHEN 'kz' THEN 'pz'
                WHEN 'k1' THEN 'p1'
                WHEN 'k2' THEN 'p2'
                WHEN 'k3' THEN 'p3'
                WHEN 'k4' THEN 'p4'
                ELSE 'pz'
            END")
        ]);

        // Step 4: Update commercial_offers table - migrate selected_factor values from k* to p*
        if (Schema::hasTable('commercial_offers')) {
            DB::table('commercial_offers')->update([
                'selected_factor' => DB::raw("CASE selected_factor
                    WHEN 'kz' THEN 'pz'
                    WHEN 'k1' THEN 'p1'
                    WHEN 'k2' THEN 'p2'
                    WHEN 'k3' THEN 'p3'
                    WHEN 'k4' THEN 'p4'
                    ELSE 'pz'
                END")
            ]);
        }

        // Step 5: Drop the k* columns from items table
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['kz', 'k1', 'k2', 'k3', 'k4']);
        });

        // Step 6: Change users.default_factor back to enum with p* values
        DB::statement("ALTER TABLE users MODIFY default_factor ENUM('pz', 'p1', 'p2', 'p3', 'p4') DEFAULT 'pz'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Re-add k* columns to items table
        Schema::table('items', function (Blueprint $table) {
            $table->float('kz')->default(1)->after('is_for_llymar');
            $table->float('k1')->default(1)->after('pz');
            $table->float('k2')->default(1)->after('p1');
            $table->float('k3')->default(1)->after('p2');
            $table->float('k4')->default(1)->after('p3');
        });

        // Step 2: Recalculate k* values from p* and purchase_price
        DB::table('items')->whereNotNull('purchase_price')->where('purchase_price', '>', 0)->update([
            'kz' => DB::raw('pz / purchase_price'),
            'k1' => DB::raw('p1 / purchase_price'),
            'k2' => DB::raw('p2 / purchase_price'),
            'k3' => DB::raw('p3 / purchase_price'),
            'k4' => DB::raw('pr / purchase_price'),
        ]);

        // Step 3: Revert users.default_factor enum to use k* values
        DB::statement("ALTER TABLE users MODIFY default_factor ENUM('kz', 'k1', 'k2', 'k3', 'k4') DEFAULT 'kz'");

        // Step 4: Update users table - migrate factor values from p* back to k*
        DB::table('users')->update([
            'default_factor' => DB::raw("CASE default_factor
                WHEN 'pz' THEN 'kz'
                WHEN 'p1' THEN 'k1'
                WHEN 'p2' THEN 'k2'
                WHEN 'p3' THEN 'k3'
                WHEN 'p4' THEN 'k4'
                ELSE 'kz'
            END")
        ]);

        // Step 5: Update orders table - migrate selected_factor values from p* back to k*
        DB::table('orders')->update([
            'selected_factor' => DB::raw("CASE selected_factor
                WHEN 'pz' THEN 'kz'
                WHEN 'p1' THEN 'k1'
                WHEN 'p2' THEN 'k2'
                WHEN 'p3' THEN 'k3'
                WHEN 'p4' THEN 'k4'
                ELSE 'kz'
            END")
        ]);

        // Step 6: Update commercial_offers table - migrate selected_factor values from p* back to k*
        if (Schema::hasTable('commercial_offers')) {
            DB::table('commercial_offers')->update([
                'selected_factor' => DB::raw("CASE selected_factor
                    WHEN 'pz' THEN 'kz'
                    WHEN 'p1' THEN 'k1'
                    WHEN 'p2' THEN 'k2'
                    WHEN 'p3' THEN 'k3'
                    WHEN 'p4' THEN 'k4'
                    ELSE 'kz'
                END")
            ]);
        }
    }
};
