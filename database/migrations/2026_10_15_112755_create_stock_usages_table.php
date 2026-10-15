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
      Schema::create('stock_usages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('department_stock_id')->constrained('department_stocks')->onDelete('cascade');
    $table->foreignId('user_id')->nullable();
    $table->integer('quantity_used'); // Jumlah yang dipakai
    $table->timestamps(); // Tanggal dan jam pemakaian tercatat otomatis
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_usages');
    }
};
