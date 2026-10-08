<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_vendors', function (Blueprint $table) {
            $table->string('marketplace')->nullable()->after('company_name');
            $table->string('payment_bank')->nullable()->after('payment_method');
            $table->decimal('subtotal', 15, 2)->nullable()->after('total_price');
            $table->decimal('shipping_cost', 15, 2)->nullable()->after('subtotal');
            $table->decimal('discount_voucher', 15, 2)->nullable()->after('shipping_cost');
            $table->decimal('tax_ppn', 15, 2)->nullable()->after('discount_voucher');
            $table->decimal('total_before_tax', 15, 2)->nullable()->after('tax_ppn');
            $table->text('notes')->nullable()->after('product_url');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_vendors', function (Blueprint $table) {
            $table->dropColumn([
                'marketplace',
                'payment_bank',
                'subtotal',
                'shipping_cost',
                'discount_voucher',
                'tax_ppn',
                'total_before_tax',
                'notes',
            ]);
        });
    }
};