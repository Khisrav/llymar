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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->change();
            $table->decimal('reward_fee', 5, 2)->default(0)->change();
            $table->string('country')->nullable()->change();
            $table->string('region')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable(false)->change();
            $table->decimal('reward_fee', 5, 2)->nullable(false)->default(null)->change();
            $table->string('country')->nullable(false)->change();
            $table->string('region')->nullable(false)->change();
        });
    }
};
