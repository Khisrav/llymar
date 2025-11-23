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
        Schema::create('commercial_offers', function (Blueprint $table) {
            $table->id();

            // User who created the offer
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Customer information
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address')->nullable();
            $table->text('customer_comment')->nullable();

            // Manufacturer information
            $table->string('manufacturer_name');
            $table->string('manufacturer_phone');

            // Arrays and objects: Laravel supports JSON columns for these
            $table->json('openings');
            $table->json('additional_items');
            $table->json('glass');
            $table->json('services');
            $table->json('cart_items');

            // Pricing
            $table->decimal('total_price', 10, 2);
            $table->decimal('markup_percentage', 5, 2)->nullable();
            $table->string('selected_factor')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commercial_offers');
    }
};
