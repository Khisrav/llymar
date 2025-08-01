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
        // Rename table from comission_credits to commission_credits
        Schema::rename('comission_credits', 'commission_credits');
        
        // Make receipt column nullable
        Schema::table('commission_credits', function (Blueprint $table) {
            $table->string('receipt')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Make receipt column required again
        Schema::table('commission_credits', function (Blueprint $table) {
            $table->string('receipt')->nullable(false)->change();
        });
        
        // Rename table back to comission_credits
        Schema::rename('commission_credits', 'comission_credits');
    }
};
