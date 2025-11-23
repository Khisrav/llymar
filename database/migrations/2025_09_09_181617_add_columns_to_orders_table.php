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
            $table->integer('factory_id')->nullable();
            $table->enum('order_type', ['Отправка', 'Самовывоз', 'Монтаж'])->default('Монтаж');
            $table->string('logistics_company')->nullable();
            $table->boolean('is_sketch_sent')->default(false);
            $table->date('readiness_date')->nullable();
            $table->float('glasses_area')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('factory_id');
            $table->dropColumn('order_type');
            $table->dropColumn('logistics_company');
            $table->dropColumn('is_sketch_sent');
            $table->dropColumn('readiness_date');
            $table->dropColumn('glasses_area');
        });
    }
};
