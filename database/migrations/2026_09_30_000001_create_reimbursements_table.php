<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reimbursements', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();          // 001/OPS-PICT/CRF/IX/2026
            $t->date('request_date');
            $t->string('requested_by');
            $t->string('nik')->nullable();
            $t->string('position')->nullable();
            $t->string('department')->nullable();
            $t->date('period_start')->nullable();
            $t->date('period_end')->nullable();
            $t->text('notes')->nullable();
            $t->string('bank_name')->nullable();
            $t->string('account_name')->nullable();
            $t->string('account_number')->nullable();
            $t->json('signers')->nullable();         // requested, checked, acknowledge, verified, approved
            $t->timestamps();
        });

        Schema::create('reimbursement_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('reimbursement_id')->constrained()->cascadeOnDelete();
            $t->string('date_label')->nullable();    // "1 Sep - 30 Sep 2026"
            $t->string('description');               // "Meal Allowance Rp25.000 x 22"
            $t->string('receipt_no')->nullable();
            $t->string('purpose')->nullable();
            $t->unsignedInteger('qty')->default(1);
            $t->string('unit')->default('Day');
            $t->unsignedBigInteger('price')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reimbursement_items');
        Schema::dropIfExists('reimbursements');
    }
};
