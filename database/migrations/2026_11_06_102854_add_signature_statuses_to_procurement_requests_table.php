<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('procurement_requests', function (Blueprint $table) {
        $table->boolean('is_pic_approved')->default(false);
        $table->boolean('is_ops_approved')->default(false);
        $table->boolean('is_fem_approved')->default(false);
        $table->boolean('is_coo_approved')->default(false);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            //
        });
    }
};
