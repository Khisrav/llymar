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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->string('item_vendor_code');
            $table->foreign('item_vendor_code')->references('vendor_code')->on('items')->onDelete('cascade');
            $table->integer('quantity');
            $table->timestamps();
            // SQLSTATE[HY000]: General error: 1005 Can't create table `llymar_db`.`order_items` (errno: 150 "Foreign key constraint is incorrectly formed") (Connection: mysql, SQL: alter table `order_items` add constraint `order_items_item_vendor_code_foreign` foreign key (`item_vendor_code`) references `items` (`vendor_code`) on delete cascade)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
