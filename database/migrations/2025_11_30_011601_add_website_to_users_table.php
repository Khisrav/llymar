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
            //add website and private_note columns
            $table->string('website')->nullable();
            $table->text('private_note')->nullable(); //примечание видное только тому у кого есть доступ к админке (оператор, супермен)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //drop website and private_note columns
            $table->dropColumn(['website', 'private_note']);
        });
    }
};
