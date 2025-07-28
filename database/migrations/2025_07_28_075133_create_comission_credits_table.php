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
        Schema::create('comission_credits', function (Blueprint $table) {
            $table->id();
            //comissions should be credited to the parent user of the order that was made by another user
            $table->foreignId('user_id')->constrained('users'); // to whom to be credited
            $table->decimal('amount', 10, 2);
            $table->foreignId('order_id')->constrained('orders'); // from which order
            $table->foreignId('parent_id')->constrained('users'); // from which user
            $table->string('receipt'); //receipt will be a pdf or image file that is attached to the record by admins when paid
            $table->string('type'); //income, expense
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comission_credits');
    }
};
