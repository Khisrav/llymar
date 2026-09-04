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
            $table->integer('a')->default(17)->change();
            $table->decimal('b', 10, 2)->default(24)->change();
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
