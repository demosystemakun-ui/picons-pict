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
        Schema::create('procurement_comparisons', function (Blueprint $table) {
    $table->id();
    $table->foreignId('procurement_request_id')->constrained()->cascadeOnDelete();
    $table->string('vendor_name');
    $table->string('company_name')->nullable();
    $table->decimal('total_price', 15, 2);
    $table->string('tax_note')->nullable();          // 'Include Tax' / 'Exclude Tax'
    $table->integer('sort_order')->default(0);       // urutan tampil (1, 2, 3)
    $table->boolean('is_selected')->default(false);  // vendor yang dipilih admin
    $table->text('reason')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
