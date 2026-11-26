<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_comparison_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procurement_request_id');
            $table->unsignedBigInteger('procurement_vendor_id')->nullable();
            $table->string('mode', 30)->nullable();
            $table->string('file_path');
            $table->string('disk', 60)->default('public');
            $table->string('original_name')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->foreign('procurement_request_id', 'fk_pca_pr_id')
                ->references('id')
                ->on('procurement_requests')
                ->cascadeOnDelete();

            $table->foreign('procurement_vendor_id', 'fk_pca_vendor_id')
                ->references('id')
                ->on('procurement_vendors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_comparison_attachments');
    }
};