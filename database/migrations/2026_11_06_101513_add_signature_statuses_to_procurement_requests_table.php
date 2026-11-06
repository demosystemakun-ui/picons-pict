<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            // Cek satu per satu untuk menghindari error duplicate column
            if (!Schema::hasColumn('procurement_requests', 'is_pic_approved')) {
                $table->boolean('is_pic_approved')->default(false);
            }
            if (!Schema::hasColumn('procurement_requests', 'is_ops_approved')) {
                $table->boolean('is_ops_approved')->default(false);
            }
            if (!Schema::hasColumn('procurement_requests', 'is_fem_approved')) {
                $table->boolean('is_fem_approved')->default(false);
            }
            if (!Schema::hasColumn('procurement_requests', 'is_coo_approved')) {
                $table->boolean('is_coo_approved')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropColumn(['is_pic_approved', 'is_ops_approved', 'is_fem_approved', 'is_coo_approved']);
        });
    }
};