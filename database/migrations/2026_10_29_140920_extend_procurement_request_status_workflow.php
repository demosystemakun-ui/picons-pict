<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('pending', 'approved', 'processed', 'received', 'completed', 'revision', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'revision') NOT NULL DEFAULT 'pending'");
    }
};
