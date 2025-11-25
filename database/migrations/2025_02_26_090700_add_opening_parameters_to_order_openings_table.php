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
            $table->float('a')->default(14);
            $table->float('b')->default(17);
            $table->float('c')->default(13);
            $table->float('d')->default(12);
            $table->float('e')->default(30);
            $table->float('f')->default(6);
            $table->float('g')->default(55);
            $table->float('i')->default(550);
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
