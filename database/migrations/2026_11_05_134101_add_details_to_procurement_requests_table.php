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
        $table->date('request_date')->nullable();
        $table->string('request_by')->nullable();
        $table->string('division')->nullable();
        $table->string('prepared_by')->nullable();
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
