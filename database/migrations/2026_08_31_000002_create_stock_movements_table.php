<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->decimal('quantity', 15, 2); // positive for in/adjustment-up, negative for out/adjustment-down
            $table->decimal('stock_before', 15, 2);
            $table->decimal('stock_after', 15, 2);

            // where the stock went (only relevant for type = out)
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            // why (only relevant for type = adjustment)
            $table->string('reason')->nullable();

            // polymorphic link back to whatever triggered this movement
            // e.g. ConsumableRequest, GoodsReceipt, StockAdjustment
            $table->nullableMorphs('reference');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['item_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
