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
        Schema::table('companies', function (Blueprint $table) {
            //nullable
            $table->string('kpp')->nullable()->change();
            $table->string('ogrn')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            //nullable
            $table->string('kpp')->nullable(false)->change();
            $table->string('ogrn')->nullable(false)->change();
        });
    }
};
