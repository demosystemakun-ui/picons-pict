<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus tabel lama jika ada (sisa dari error sebelumnya)
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::enableForeignKeyConstraints();

        // Tabel Pembelian Utama
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('procurement_vendors')->nullOnDelete();

            // KUNCI PERBAIKAN: 100 karakter agar aman di MySQL lama (767 byte limit)
            $table->string('po_number', 100)->unique();

            $table->date('order_date');
            $table->date('estimated_arrival')->nullable();
            $table->string('status')->default('pending'); // pending, ordered, partially_received, completed
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Tabel Item Pembelian
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->string('product_name', 191);
            $table->integer('qty_ordered');
            $table->integer('qty_received')->default(0);
            $table->integer('qty_damaged')->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::enableForeignKeyConstraints();
    }
};