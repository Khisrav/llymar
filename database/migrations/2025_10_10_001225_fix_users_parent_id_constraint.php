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
            // Drop the existing foreign key constraint
            $table->dropForeign(['parent_id']);
            
            // Make parent_id nullable and remove default
            $table->unsignedBigInteger('parent_id')->nullable()->change();
            
            // Add the foreign key constraint with CASCADE on delete
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['parent_id']);
            
            // Restore the old default and constraint
            $table->unsignedBigInteger('parent_id')->default(1)->change();
            
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('users');
        });
    }
};
