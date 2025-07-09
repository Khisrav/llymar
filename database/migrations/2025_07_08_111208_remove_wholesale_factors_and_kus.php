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
        // Remove wholesale_factor_key from users table
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'wholesale_factor_key')) {
                $table->dropColumn('wholesale_factor_key');
            }
        });

        // Remove reduction_factors from categories table
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'reduction_factors')) {
                $table->dropColumn('reduction_factors');
            }
        });

        // Drop wholesale_factors table
        Schema::dropIfExists('wholesale_factors');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate wholesale_factors table
        Schema::create('wholesale_factors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->float('value')->default(1);
            $table->string('reduction_factor_key')->default('KU1');
            $table->string('group_name')->unique();
            $table->string('color')->nullable();
            $table->timestamps();
        });

        // Add reduction_factors back to categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->json('reduction_factors')->nullable();
        });

        // Add wholesale_factor_key back to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('wholesale_factor_key')->nullable();
        });
    }
};
