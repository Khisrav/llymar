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
            $table->string('inn')->nullable();
            $table->string('kpp')->nullable();
            $table->string('current_account')->nullable(); //расчетный счет
            $table->string('correspondent_account')->nullable(); //корреспондентский счет
            $table->string('bik')->nullable();
            $table->string('bank')->nullable();
            $table->string('legal_address')->nullable();
            $table->string('role')->default('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
