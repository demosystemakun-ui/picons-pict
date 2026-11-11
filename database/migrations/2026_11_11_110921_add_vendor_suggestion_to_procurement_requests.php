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
    Schema::table('procurement_requests', function (Blueprint $table) {
        $table->text('vendor_suggestion')->nullable()->after('required_spec');
    });
}

public function down(): void
{
    Schema::table('procurement_requests', function (Blueprint $table) {
        $table->dropColumn('vendor_suggestion');
    });
}
};
