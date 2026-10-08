<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_logs', 'department_id')) {
                $table->foreignId('department_id')->nullable()
                      ->constrained('departments')->nullOnDelete();
            }
            if (!Schema::hasColumn('stock_logs', 'item_id')) {
                $table->foreignId('item_id')->nullable()
                      ->constrained('items')->nullOnDelete();
            }
            if (!Schema::hasColumn('stock_logs', 'quantity')) {
                $table->decimal('quantity', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('stock_logs', 'user_id')) {
                $table->foreignId('user_id')->nullable()
                      ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('stock_logs', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_logs', function (Blueprint $table) {
            foreach (['department_id', 'item_id', 'user_id'] as $col) {
                if (Schema::hasColumn('stock_logs', $col)) {
                    $table->dropConstrainedForeignId($col);
                }
            }
            foreach (['quantity', 'notes'] as $col) {
                if (Schema::hasColumn('stock_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};