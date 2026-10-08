<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_vendor_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_vendor_id')
                  ->constrained('procurement_vendors')
                  ->cascadeOnDelete();

            $table->string('product_name');
            $table->string('variant')->nullable();
            $table->string('sku')->nullable();
            $table->string('image_url')->nullable();

            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);

            $table->string('stock_status')->nullable();
            $table->decimal('weight', 8, 2)->nullable();

            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_vendor_items');
    }
};