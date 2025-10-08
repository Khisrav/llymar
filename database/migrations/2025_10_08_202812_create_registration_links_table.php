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
        Schema::create('registration_links', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->decimal('reward_fee', 5, 2)->default(0);
            $table->boolean('can_access_dxf')->default(false);
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->foreignId('registered_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index('token');
            $table->index('created_by');
            $table->index('is_used');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_links');
    }
};
