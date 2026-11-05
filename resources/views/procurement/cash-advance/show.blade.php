<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            // Pilihan: 'po', 'cash_advance', 'reimbursement'
            // Default 'po' agar PR lama tetap aman
            $table->string('procurement_method')->default('po')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropColumn('procurement_method');
        });
    }
};