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
        Schema::table('warehouse_records', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouse_records', 'warehouse_id')) {
                $table->unsignedBigInteger('warehouse_id');
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_records', function (Blueprint $table) {
            //
        });
    }
};
