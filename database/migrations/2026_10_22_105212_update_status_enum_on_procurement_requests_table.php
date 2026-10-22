<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan 'revision' ke daftar ENUM
        DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'revision') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Kembalikan ke daftar ENUM sebelumnya (hati-hati jika ada data 'revision')
        DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};