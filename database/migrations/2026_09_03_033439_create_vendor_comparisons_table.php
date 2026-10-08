<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_comparisons', function (Blueprint $table) {
            $table->id();$table->foreignId('procurement_request_id')->constrained()->cascadeOnDelete();
            $table->string('vendor_name'); // Contoh: Monotaro, Tokopedia, Shopee$table->string('item_name');
            $table->decimal('price', 15, 2);$table->integer('quantity');
            $table->decimal('shipping_cost', 15, 2)->default(0);$table->decimal('total_price', 15, 2);
            $table->text('notes')->nullable(); // Spesifikasi atau keunggulan barang$table->string('screenshot_path')->nullable();
            $table->boolean('is_selected')->default(false); // Penanda vendor mana yang dipilih$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_comparisons');
    }
};