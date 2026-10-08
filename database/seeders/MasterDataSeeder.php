<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Departments
        $departments = [
            ['code' => 'IT', 'name' => 'Information Technology', 'cost_center' => 'CC-001'],
            ['code' => 'HRD', 'name' => 'Human Resources', 'cost_center' => 'CC-002'],
            ['code' => 'FIN', 'name' => 'Finance', 'cost_center' => 'CC-003'],
            ['code' => 'OPS', 'name' => 'Operations', 'cost_center' => 'CC-004'],
        ];
        foreach ($departments as $d) {
            Department::firstOrCreate(['code' => $d['code']], $d);
        }

        // Categories
        $categories = [
            ['code' => 'ATK', 'name' => 'Alat Tulis Kantor'],
            ['code' => 'IT-SUP', 'name' => 'IT Supplies'],
            ['code' => 'JAN', 'name' => 'Janitorial / Kebersihan'],
            ['code' => 'PPE', 'name' => 'Alat Pelindung Diri (K3)'],
        ];
        foreach ($categories as $c) {
            Category::firstOrCreate(['code' => $c['code']], $c);
        }

        // Units
        $units = [
            ['code' => 'PCS', 'name' => 'Pieces'],
            ['code' => 'BOX', 'name' => 'Box'],
            ['code' => 'PACK', 'name' => 'Pack'],
            ['code' => 'RIM', 'name' => 'Rim'],
            ['code' => 'LTR', 'name' => 'Liter'],
        ];
        foreach ($units as $u) {
            Unit::firstOrCreate(['code' => $u['code']], $u);
        }

        // Vendors
        $vendors = [
            ['code' => 'VND-001', 'name' => 'Monotaro Indonesia', 'vendor_type' => 'monotaro', 'rating' => 4.5],
            ['code' => 'VND-002', 'name' => 'Toko Shopee Official', 'vendor_type' => 'shopee', 'rating' => 4.2],
            ['code' => 'VND-003', 'name' => 'Toko Tokopedia Mall', 'vendor_type' => 'tokopedia', 'rating' => 4.0],
            ['code' => 'VND-004', 'name' => 'CV Sumber Rejeki (Lokal)', 'vendor_type' => 'local', 'rating' => 3.8],
        ];
        foreach ($vendors as $v) {
            Vendor::firstOrCreate(['code' => $v['code']], $v);
        }

        // Demo users, one per role
        $itDept = Department::where('code', 'IT')->first();
        $demoUsers = [
            ['name' => 'Super Admin', 'email' => 'admin@company.com', 'role' => 'Super Admin'],
            ['name' => 'Warehouse Staff', 'email' => 'warehouse@company.com', 'role' => 'Warehouse Admin'],
            ['name' => 'Procurement Staff', 'email' => 'procurement@company.com', 'role' => 'Procurement'],
            ['name' => 'Dept User IT', 'email' => 'user@company.com', 'role' => 'Department User'],
            ['name' => 'Manager IT', 'email' => 'manager@company.com', 'role' => 'Manager'],
            ['name' => 'Finance Staff', 'email' => 'finance@company.com', 'role' => 'Finance'],
        ];

        foreach ($demoUsers as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'department_id' => $itDept?->id,
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            $user->syncRoles([$u['role']]);
        }
    }
}
