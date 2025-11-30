<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any existing NULL values to 0
        \DB::table('users')->whereNull('reward_fee')->update(['reward_fee' => 0]);
        
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('reward_fee', 5, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('reward_fee', 5, 2)->nullable()->default(null)->change();
        });
    }
};
