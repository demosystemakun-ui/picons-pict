<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('department_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->integer('stock')->default(0); // Jumlah stok di departemen tersebut
            $table->timestamps();

            // Mencegah duplikasi baris untuk departemen & item yang sama
            $table->unique(['department_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_stocks');
    }
};