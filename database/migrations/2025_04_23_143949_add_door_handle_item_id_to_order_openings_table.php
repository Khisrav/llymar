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
        Schema::table('order_openings', function (Blueprint $table) {
            $table->unsignedBigInteger('door_handle_item_id')->nullable();
            $table->foreign('door_handle_item_id')->references('id')->on('items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_openings', function (Blueprint $table) {
            //
        });
    }
};
