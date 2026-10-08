<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumable_requests', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke User (Opsional, jika sistem Anda pakai login)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Section 1: Informasi Umum
            $table->date('date');
            $table->string('subject');
            
            // Note
            $table->string('general_note')->nullable();
            
            // Section 3: Signatures / Approval
            $table->string('request_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('verified_by')->nullable();

            // Status Dokumen (Draft, Pending, Approved, dll)
            $table->string('status')->default('pending');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumable_requests');
    }
};