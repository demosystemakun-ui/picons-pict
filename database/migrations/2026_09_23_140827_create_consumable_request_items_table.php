<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumable_request_items', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel induk (Cascade agar jika parent dihapus, item otomatis terhapus)
            $table->foreignId('consumable_request_id')
                  ->constrained('consumable_requests')
                  ->cascadeOnDelete();
            
            // Item details (Name & Specs)
            $table->text('item_details');
            
            // Purpose (Dipecah menjadi 3 sesuai form frontend)
            $table->text('purpose_1'); // 1. Why you want to purchase it?
            $table->text('purpose_2'); // 2. Which process or activity will the item support?
            $table->text('purpose_3'); // 3. What operational or financial efficiency will be gained?
            
            // Qty & Unit (Quantity pakai decimal agar bisa input koma misal: 1.5)
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 50);
            
            // Picture (Menyimpan nama file / path gambar)
            $table->string('picture')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumable_request_items');
    }
};