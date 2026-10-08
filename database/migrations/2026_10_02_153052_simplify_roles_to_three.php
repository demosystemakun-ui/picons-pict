<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Role lama => role baru (ubah sesuai kebutuhan)
        $map = [
            'Department User' => 'User',
            'Warehouse Admin' => 'Admin',
            'Procurement'     => 'Admin',
            'Manager'         => 'Admin',
            'Finance'         => 'Admin',
        ];

        // Pastikan 3 role utama ada
        foreach (['Super Admin', 'Admin', 'User'] as $name) {
            $exists = DB::table('roles')->where('name', $name)->where('guard_name', 'web')->exists();
            if (!$exists) {
                DB::table('roles')->insert([
                    'name' => $name, 'guard_name' => 'web',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        $id = fn ($n) => DB::table('roles')->where('name', $n)->where('guard_name', 'web')->value('id');

        foreach ($map as $old => $new) {
            $oldId = $id($old);
            if (!$oldId) continue;

            // Pindahkan user ke role baru
            DB::update('UPDATE IGNORE model_has_roles SET role_id = ? WHERE role_id = ?', [$id($new), $oldId]);

            // Bersihkan sisa dan hapus role lama
            DB::table('model_has_roles')->where('role_id', $oldId)->delete();
            DB::table('role_has_permissions')->where('role_id', $oldId)->delete();
            DB::table('roles')->where('id', $oldId)->delete();
        }
    }

    public function down(): void
    {
        // Tidak bisa dikembalikan otomatis
    }
};