<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_requests', function (Blueprint $table) {
            $table->id();

            // Header
            $table->string('no')->unique();
            $table->date('date');
            $table->string('request_by');
            $table->string('division');
            $table->string('prepared_by');
            $table->string('description');

            // Order type checkboxes, stored as JSON array: ["new_order","goods"]
            $table->json('order_type')->nullable();

            // Justification
            $table->text('background')->nullable();
            $table->text('purpose')->nullable();
            $table->text('required_spec')->nullable();

            // Item request
            $table->string('item_name');
            $table->string('item_requirement_note')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->text('target_purchase')->nullable();

            // Approval chain (names as free text per the current form;
            // can be normalized to user_id foreign keys later if needed)
            $table->string('pic_name')->nullable();
            $table->string('ops_manager_name')->nullable();
            $table->string('fem_manager_name')->nullable();
            $table->string('coo_name')->nullable();

            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_requests');
    }
};