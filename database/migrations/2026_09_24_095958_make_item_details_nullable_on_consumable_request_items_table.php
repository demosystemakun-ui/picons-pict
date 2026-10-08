<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumable_request_items', function (Blueprint $table) {
            $table->text('item_details')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('consumable_request_items', function (Blueprint $table) {
            $table->text('item_details')->nullable(false)->change();
        });
    }
};