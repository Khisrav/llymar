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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('short_name');
            $table->string('full_name');
            $table->string('boss');
            $table->string('boss_title');
            $table->string('legal_address');
            $table->string('email');
            // $table->string('phone');
            $table->string('website');
            $table->string('current_account');
            $table->string('correspondent_account');
            $table->string('bank_name');
            $table->string('bank_address');
            $table->string('bik');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
