<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->string('source')->default('manual')->after('no'); // 'manual' atau 'consumable'
            $table->unsignedBigInteger('merged_from_id')->nullable()->after('source'); // ID user/admin yg merge
        });

        // Asumsi tabel item PR bernama 'procurement_request_items'
        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->foreignId('consumable_request_item_id')->nullable()->constrained('consumable_request_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropColumn(['source', 'merged_from_id']);
        });
        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->dropForeign(['consumable_request_item_id']);
            $table->dropColumn('consumable_request_item_id');
        });
    }
};