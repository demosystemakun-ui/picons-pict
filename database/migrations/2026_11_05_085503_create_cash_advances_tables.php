<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_advances', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable()->unique(); // Diisi manual oleh Admin
            $table->foreignId('procurement_request_id')->nullable()->constrained()->nullOnDelete();
            $table->date('request_date');
            $table->string('requested_by');
            $table->string('nik')->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->string('cost_department')->nullable();
            $table->string('delivery_address')->nullable();
            $table->text('attachment')->nullable(); // Nama file / link
            $table->text('notes')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->json('signers')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_advance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_advance_id')->constrained()->cascadeOnDelete();
            $table->string('item_description');
            $table->string('purpose')->nullable();
            $table->integer('qty')->default(1);
            $table->string('unit')->default('Unit');
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_advance_items');
        Schema::dropIfExists('cash_advances');
    }
};