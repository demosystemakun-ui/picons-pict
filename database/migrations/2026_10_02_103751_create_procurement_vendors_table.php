<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_request_id')
                  ->constrained('procurement_requests')
                  ->cascadeOnDelete();
            $table->string('vendor_name');
            $table->string('company_name')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('estimated_delivery')->nullable();
            $table->text('product_url')->nullable();
            $table->decimal('total_price', 15, 2);
            $table->string('tax_note')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_winner')->default(false);
            $table->timestamps();

            $table->index(['procurement_request_id', 'total_price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_vendors');
    }
};