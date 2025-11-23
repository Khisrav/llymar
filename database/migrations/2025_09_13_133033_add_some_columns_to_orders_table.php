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
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_packed')->default(false);
            $table->boolean('is_sworn')->default(false);
            $table->boolean('is_painted')->default(false);
            $table->boolean('is_cut')->default(false);
            $table->string('glass_acceptance')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_completed');
            $table->dropColumn('is_packed');
            $table->dropColumn('is_sworn');
            $table->dropColumn('is_painted');
            $table->dropColumn('is_cut');
            $table->dropColumn('glass_acceptance');
        });
    }
};
