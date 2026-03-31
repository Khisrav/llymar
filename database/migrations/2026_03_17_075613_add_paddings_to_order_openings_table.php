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
            //ot1, ot2, ot3, ot4, zr (ot -> отступ, zr -> зазор)
            $table->integer('ot1')->default(0);
            $table->integer('ot2')->default(0);
            $table->integer('ot3')->default(0);
            $table->integer('ot4')->default(0);
            $table->integer('zr')->default(0);
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
