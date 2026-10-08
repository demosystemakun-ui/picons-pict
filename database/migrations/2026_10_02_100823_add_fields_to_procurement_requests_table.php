<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      // database/migrations/xxxx_add_fields_to_procurement_requests_table.php
Schema::table('procurement_requests', function (Blueprint $table) {
    $table->string('budget_type')->nullable();     // 'budgeted' | 'non_budgeted'
    $table->text('reason_choose_vendor')->nullable();
    $table->foreignId('final_vendor_id')->nullable(); // vendor terpilih
    $table->decimal('final_total_price', 15, 2)->nullable();
    $table->string('accounting_mgr_name')->nullable();
    $table->string('ceo_name')->nullable();
    $table->string('cfo_name')->nullable();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            //
        });
    }
};
