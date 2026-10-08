<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'barcode')) {
                $table->string('barcode')->nullable()->unique()->after('item_code');
            }
            if (!Schema::hasColumn('items', 'current_stock')) {
                $table->decimal('current_stock', 15, 2)->default(0)->after('minimum_stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['barcode', 'current_stock']);
        });
    }
};
