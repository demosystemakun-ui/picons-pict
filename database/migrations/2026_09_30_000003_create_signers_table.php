<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('title');
            $t->string('slot');                       // checked | acknowledge | verified | approved
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        $now = now();
        DB::table('signers')->insert([
            ['name' => 'Fumiya Ota',      'title' => 'Manager Operational', 'slot' => 'checked',     'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Takumi Nibe',     'title' => 'COO',                 'slot' => 'acknowledge', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ahmad Rizaldi',   'title' => 'CFO',                 'slot' => 'verified',    'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Hiroyuki Yazawa', 'title' => 'President Director',  'slot' => 'approved',    'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('signers');
    }
};