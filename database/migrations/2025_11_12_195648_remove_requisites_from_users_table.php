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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'inn',
                'kpp',
                'current_account',
                'correspondent_account',
                'bik',
                'bank',
                'legal_address',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('inn')->nullable();
            $table->string('kpp')->nullable();
            $table->string('current_account')->nullable();
            $table->string('correspondent_account')->nullable();
            $table->string('bik')->nullable();
            $table->string('bank')->nullable();
            $table->text('legal_address')->nullable();
        });
    }
};
