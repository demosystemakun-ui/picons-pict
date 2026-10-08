<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_request_items', function (Blueprint $table) {
            // Guard: hanya tambah kalau kolom belum ada
            if (!Schema::hasColumn('procurement_request_items', 'brand')) {
                $table->string('brand')->nullable()->after('item_name');
            }
            if (!Schema::hasColumn('procurement_request_items', 'type')) {
                $table->string('type')->nullable()->after('brand');
            }
            if (!Schema::hasColumn('procurement_request_items', 'model')) {
                $table->string('model')->nullable()->after('type');
            }
            if (!Schema::hasColumn('procurement_request_items', 'capacity')) {
                $table->string('capacity')->nullable()->after('model');
            }
            if (!Schema::hasColumn('procurement_request_items', 'specs')) {
                $table->text('specs')->nullable()->after('capacity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->dropColumn(['brand', 'type', 'model', 'capacity', 'specs']);
        });
    }
};  