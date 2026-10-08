<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_request_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->text('item_requirement_note')->nullable();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->string('unit')->nullable();
            $table->string('picture')->nullable();
            $table->timestamps();
                $table->string('brand')->nullable()->after('item_name');
                $table->string('type')->nullable()->after('brand');
                $table->string('model')->nullable()->after('type');
                $table->string('capacity')->nullable()->after('model');
                $table->text('specs')->nullable()->after('capacity');
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_request_items');
    }
};