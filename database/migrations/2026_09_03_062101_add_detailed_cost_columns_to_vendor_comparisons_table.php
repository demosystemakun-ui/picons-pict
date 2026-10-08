<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_comparisons', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_comparisons', 'discount_voucher')) {
                $table->decimal('discount_voucher', 15, 2)->default(0)->after('service_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendor_comparisons', function (Blueprint $table) {$table->dropColumn(['discount_voucher']);
        });
    }
};