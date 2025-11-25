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
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('counterparty_type');
            $table->string('counterparty_fullname');
            $table->string('counterparty_phone');
            $table->unsignedBigInteger('template_id');
            $table->foreign('template_id')->references('id')->on('contract_templates');
            $table->date('date')->nullable();
            $table->string('department_code');
            $table->string('index')->nullable();
            // $table->string('factory_id');
            $table->string('installation_address');
            $table->integer('price')->default(0);
            $table->integer('advance_payment_percentage')->default(0);
            //...
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            //
        });
    }
};
