<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            // Cek dulu apakah kolom sudah ada sebelum menambahkan
            if (!Schema::hasColumn('procurement_requests', 'is_accounting_approved')) {
                $table->boolean('is_accounting_approved')->default(false)->after('is_coo_approved');
            }
            if (!Schema::hasColumn('procurement_requests', 'is_cfo_approved')) {
                $table->boolean('is_cfo_approved')->default(false)->after('is_accounting_approved');
            }
            if (!Schema::hasColumn('procurement_requests', 'is_ceo_approved')) {
                $table->boolean('is_ceo_approved')->default(false)->after('is_cfo_approved');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            // Drop hanya jika kolomnya ada
            if (Schema::hasColumn('procurement_requests', 'is_accounting_approved')) {
                $table->dropColumn('is_accounting_approved');
            }
            if (Schema::hasColumn('procurement_requests', 'is_cfo_approved')) {
                $table->dropColumn('is_cfo_approved');
            }
            if (Schema::hasColumn('procurement_requests', 'is_ceo_approved')) {
                $table->dropColumn('is_ceo_approved');
            }
        });
    }
};