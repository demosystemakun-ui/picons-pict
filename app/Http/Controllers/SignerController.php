<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Opsi A: Jika Anda ingin tetap menggunakan ENUM, tambahkan 'revision'
        // Pastikan semua opsi ENUM yang lama ditulis ulang agar tidak hilang
        DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'revision') NOT NULL DEFAULT 'pending'");
        
        // --- ATAU ---

        // Opsi B: Ubah menjadi VARCHAR (lebih fleksibel untuk ke depannya)
        // Schema::table('procurement_requests', function (Blueprint $table) {
        //     $table->string('status')->change();
        // });
    }

    public function down(): void
    {
        // Kembalikan ke struktur semula jika di-rollback
        DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};