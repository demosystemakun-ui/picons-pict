<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Item;
use App\Models\DepartmentStock;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Jalankan seeder utama terlebih dahulu
        $this->call([
            RolePermissionSeeder::class,
            MasterDataSeeder::class,
        ]);

        // 2. Buat akun Admin
        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@company.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        if (method_exists($admin, 'assignRole')) {
            $admin->assignRole('Super Admin');
        }

        // 3. Ambil atau buat data pendukung (Category, Unit, Department)
        $department = Department::first();
        $category = Category::first();
        $unit = Unit::first();

        // Jika master data dari MasterDataSeeder kosong, buat data cadangan
        if (!$department) {
            $department = Department::create(['name' => 'Information Technology', 'code' => 'IT']);
        }
        if (!$category) {
            $category = Category::create(['name' => 'General Consumables']);
        }
        if (!$unit) {
            $unit = Unit::create(['name' => 'Pcs', 'code' => 'PCS']);
        }

        // 4. Buat Item dengan menyertakan category_id dan unit_id yang wajib diisi
        $item1 = Item::firstOrCreate(
            ['item_code' => 'ITM-001'],
            [
                'item_name' => 'Kertas A4',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'current_stock' => 100
            ]
        );

        $item2 = Item::firstOrCreate(
            ['item_code' => 'ITM-002'],
            [
                'item_name' => 'Tinta Printer Black',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'current_stock' => 50
            ]
        );

        // 5. Masukkan data dummy stok ke department_stocks
        DepartmentStock::firstOrCreate(
            ['department_id' => $department->id, 'item_id' => $item1->id],
            ['stock' => 25]
        );
        
        DepartmentStock::firstOrCreate(
            ['department_id' => $department->id, 'item_id' => $item2->id],
            ['stock' => 10]
        );
    }
}