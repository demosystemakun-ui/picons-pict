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
    Schema::table('procurement_request_items', function (Blueprint $table) {
        $table->foreignId('consumable_request_item_id')
              ->nullable()
              ->after('procurement_request_id')
              ->constrained('consumable_request_items')
              ->nullOnDelete();

        $table->foreignId('department_id')
              ->nullable()
              ->after('consumable_request_item_id')
              ->constrained('departments')
              ->nullOnDelete();
    });
}

public function down()
{
    Schema::table('procurement_request_items', function (Blueprint $table) {
        $table->dropForeign(['consumable_request_item_id']);
        $table->dropForeign(['department_id']);
        $table->dropColumn(['consumable_request_item_id', 'department_id']);
    });
}
};
