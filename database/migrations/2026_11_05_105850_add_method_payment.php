<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            // Cek dulu, hanya tambahkan jika kolom belum ada
            if (!Schema::hasColumn('procurement_requests', 'procurement_method')) {
                $table->string('procurement_method')->default('po')->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            if (Schema::hasColumn('procurement_requests', 'procurement_method')) {
                $table->dropColumn('procurement_method');
            }
        });
    }
};