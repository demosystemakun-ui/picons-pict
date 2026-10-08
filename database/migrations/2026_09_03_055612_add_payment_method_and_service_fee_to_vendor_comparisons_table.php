<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_comparisons', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_comparisons', 'payment_method')) {
                $table->string('payment_method')->nullable();
            }
            if (!Schema::hasColumn('vendor_comparisons', 'service_fee')) {
                $table->decimal('service_fee', 15, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendor_comparisons', function (Blueprint $table) {$table->dropColumn(['payment_method', 'service_fee']);
        });
    }
};