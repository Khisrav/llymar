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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            // $table->string('counterparty_type');
            // $table->string('counterparty_fullname');
            // $table->string('counterparty_phone');
            // $table->unsignedBigInteger('template_id');
            // $table->date('date')->default('');
            // $table->string('department_code');
            // $table->string('index');
            // $table->string('factory');
            // $table->string('installation_address');
            // $table->integer('price')->default(0);
            // $table->integer('advance_payment_percentage')->default(0);
            // //...
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
