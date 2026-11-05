<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::table('procurement_requests', function (Blueprint $table) {
        if (!Schema::hasColumn('procurement_requests', 'budget_type')) {
            $table->string('budget_type')->nullable()->after('procurement_method');
        }
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
