<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('procurement_request_items', function (Blueprint $table) {
            // Cek dulu, hanya tambahkan jika kolom belum ada
            if (!Schema::hasColumn('procurement_request_items', 'consumable_request_item_id')) {
                $table->foreignId('consumable_request_item_id')
                      ->nullable()
                      ->after('procurement_request_id')
                      ->constrained('consumable_request_items')
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('procurement_request_items', 'department_id')) {
                $table->foreignId('department_id')
                      ->nullable()
                      ->after('consumable_request_item_id')
                      ->constrained('departments')
                      ->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('procurement_request_items', function (Blueprint $table) {
            // Cek dulu, hanya drop jika kolom ada
            if (Schema::hasColumn('procurement_request_items', 'consumable_request_item_id')) {
                $table->dropForeign(['consumable_request_item_id']);
                $table->dropColumn('consumable_request_item_id');
            }

            if (Schema::hasColumn('procurement_request_items', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
        });
    }
};