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
            $table->datetime('sketched_at')->nullable();
            $table->datetime('cut_at')->nullable();
            $table->datetime('painted_at')->nullable();
            $table->datetime('packed_at')->nullable();
            $table->datetime('sworn_at')->nullable();
            $table->datetime('glass_rework_at')->nullable();//переделка стекла
            $table->datetime('glass_complaint_at')->nullable();//рекламация стекла
            $table->datetime('glass_ready_at')->nullable();//готовность стекла
            $table->datetime('completed_at')->nullable();
            $table->text('technical_comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('sketched_at');
            $table->dropColumn('cut_at');
            $table->dropColumn('painted_at');
            $table->dropColumn('packed_at');
            $table->dropColumn('sworn_at');
            $table->dropColumn('glass_rework_at');
            $table->dropColumn('glass_complaint_at');
            $table->dropColumn('glass_ready_at');
            $table->dropColumn('completed_at');
            $table->dropColumn('technical_comment');
        });
    }
};
