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
        Schema::table('orders', function (Blueprint $table) {
            // Add company_id foreign key (nullable since not all roles require it)
            $table->unsignedBigInteger('company_id')->nullable()->after('user_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            
            // Add company_bill_id foreign key (nullable since only G1 requires it)
            $table->unsignedBigInteger('company_bill_id')->nullable()->after('company_id');
            $table->foreign('company_bill_id')->references('id')->on('company_bills')->onDelete('set null');
            
            // Add delivery_option to track which tab was selected (montazh, dostavka, tk, samovivoz)
            $table->string('delivery_option')->nullable()->after('order_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['company_id']);
            $table->dropForeign(['company_bill_id']);
            
            // Drop columns
            $table->dropColumn(['company_id', 'company_bill_id', 'delivery_option']);
        });
    }
};
