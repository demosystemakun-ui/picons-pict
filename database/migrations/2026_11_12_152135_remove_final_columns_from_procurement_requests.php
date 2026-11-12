<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            // Hapus kolom-kolom yang bukan milik PR Tahap 1
            $table->dropColumn([
                'final_number', 
                'final_total_price', 
                'budget_type', 
                'reason_choose_vendor',
                'accounting_mgr_name', 
                'cfo_name', 
                'ceo_name',
                'is_accounting_approved', 
                'is_cfo_approved', 
                'is_ceo_approved'
            ]);
        });
    }

    public function down(): void {}
};