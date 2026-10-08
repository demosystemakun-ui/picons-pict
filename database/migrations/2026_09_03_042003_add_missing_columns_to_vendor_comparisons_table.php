<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_comparisons', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_comparisons', 'item_name')) {
                $table->string('item_name')->nullable();
            }
            if (!Schema::hasColumn('vendor_comparisons', 'estimated_delivery')) {
                $table->string('estimated_delivery')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendor_comparisons', function (Blueprint $table) {
            $table->dropColumn(['item_name', 'estimated_delivery']);
        });
    }
};