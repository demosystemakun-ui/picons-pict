<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_archives', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable'); // Untuk relasi ke ProcurementRequest atau Pembelian
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->string('category')->default('ai_scan'); // ai_scan, invoice, faktur
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_archives');
    }
};