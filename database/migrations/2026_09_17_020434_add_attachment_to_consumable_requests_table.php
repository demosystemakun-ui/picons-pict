<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('consumable_requests', function (Blueprint $table) {
            // Kolom untuk menyimpan path file gambar/attachment
            $table->string('attachment')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('consumable_requests', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
    }
};