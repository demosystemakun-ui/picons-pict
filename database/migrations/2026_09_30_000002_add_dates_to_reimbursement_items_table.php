<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reimbursement_items', function (Blueprint $t) {
            $t->date('date_from')->nullable()->after('reimbursement_id');
            $t->date('date_to')->nullable()->after('date_from');
        });
    }

    public function down(): void
    {
        Schema::table('reimbursement_items', function (Blueprint $t) {
            $t->dropColumn(['date_from', 'date_to']);
        });
    }
};