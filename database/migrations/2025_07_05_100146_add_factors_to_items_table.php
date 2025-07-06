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
        Schema::table('items', function (Blueprint $table) {
            $table->float('kz')->default(1);
            $table->float('pz')->default(0);
            
            $table->float('k1')->default(1);
            $table->float('p1')->default(0);
            
            $table->float('k2')->default(1);
            $table->float('p2')->default(0);
            
            $table->float('k3')->default(1);
            $table->float('p3')->default(0);
            
            $table->float('k4')->default(1);
            $table->float('pr')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            //
        });
    }
};
