<?php
// database/migrations/xxxx_add_revision_note_to_consumable_requests.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumable_requests', function (Blueprint $table) {
            $table->text('revision_note')->nullable()->after('status');
            $table->timestamp('revision_requested_at')->nullable()->after('revision_note');
        });
    }

    public function down(): void
    {
        Schema::table('consumable_requests', function (Blueprint $table) {
            $table->dropColumn(['revision_note', 'revision_requested_at']);
        });
    }
};