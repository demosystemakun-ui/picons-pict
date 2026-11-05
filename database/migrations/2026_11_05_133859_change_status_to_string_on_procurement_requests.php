<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Tambahkan ini

return new class extends Migration
{
    public function up(): void
    {
        // Menggunakan Raw SQL untuk mengubah ENUM menjadi VARCHAR(50)
        // Ini cara paling aman di Laravel 11/12/13 tanpa doctrine/dbal
        DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // (Opsional) Kembalikan ke ENUM jika di-rollback
        // Pastikan daftar ENUM di bawah sesuai dengan kondisi awal database Anda
        DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('pending', 'approved', 'processed') NOT NULL DEFAULT 'pending'");
    }
};