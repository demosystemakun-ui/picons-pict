<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidations', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable()->unique();
            $table->foreignId('procurement_request_id')->nullable()->constrained()->nullOnDelete();
            $table->date('request_date');
            $table->string('requested_by');
            $table->string('nik')->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->text('notes')->nullable();
            $table->json('signers')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('liquidation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('liquidation_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->integer('qty')->default(1);
            $table->string('unit')->default('Unit');
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidation_items');
        Schema::dropIfExists('liquidations');
    }
};