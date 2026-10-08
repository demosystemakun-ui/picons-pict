<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->boolean('is_new_order')->default(false)->after('description');
            $table->boolean('is_repeat_order')->default(false)->after('is_new_order');
            $table->boolean('is_goods')->default(false)->after('is_repeat_order');
            $table->boolean('is_services')->default(false)->after('is_goods');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropColumn(['is_new_order', 'is_repeat_order', 'is_goods', 'is_services']);
        });
    }
};