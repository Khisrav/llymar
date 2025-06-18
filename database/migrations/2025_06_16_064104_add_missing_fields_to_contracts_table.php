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
            // Add missing fields that are used in ContractResource
            $table->unsignedBigInteger('company_performer_id')->nullable();
            $table->foreign('company_performer_id')->references('id')->on('companies')->onDelete('set null');
            
            $table->unsignedBigInteger('company_factory_id')->nullable();
            $table->foreign('company_factory_id')->references('id')->on('companies')->onDelete('set null');
            
            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            
            $table->string('counterparty_address')->nullable();
            $table->string('counterparty_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['company_performer_id']);
            $table->dropColumn('company_performer_id');
            
            $table->dropForeign(['company_factory_id']);
            $table->dropColumn('company_factory_id');
            
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
            
            $table->dropColumn('counterparty_address');
            $table->dropColumn('counterparty_email');
        });
    }
};
