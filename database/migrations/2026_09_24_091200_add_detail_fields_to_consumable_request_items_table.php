<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumable_request_items', function (Blueprint $table) {
            $table->string('item_name')->nullable()->after('consumable_request_id');
            $table->string('brand')->nullable()->after('item_name');
            $table->string('type')->nullable()->after('brand');
            $table->string('model')->nullable()->after('type');
            $table->string('capacity')->nullable()->after('model');
            $table->text('specs')->nullable()->after('capacity');
        });
    }

    public function down(): void
    {
        Schema::table('consumable_request_items', function (Blueprint $table) {
            $table->dropColumn(['item_name', 'brand', 'type', 'model', 'capacity', 'specs']);
        });
    }
};