<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Item;
use App\Models\Stock;
use App\Models\Department;

class InventoryStockController extends Controller
{
    // ... (Method lain kamu yang sudah ada: index, create, store, dll)

    /**
     * ═══════════════════════════════════════════════════════════
     * PROCESS IMPORT EXCEL DENGAN CUSTOM MAPPING
     * ═══════════════════════════════════════════════════════════
     */
    public function importProcess(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'excel_file'          => 'required|mimes:xlsx,xls,csv|max:5120',
            'mapping'             => 'required|array',
            'mapping.item_code'   => 'required|string',
            'mapping.item_name'   => 'required|string',
            'mapping.stock'       => 'required|string',
            'mapping.department'  => 'required|string',
        ]);

        $file    = $request->file('excel_file');
        $mapping = $request->input('mapping');

        // 2. Baca File Excel (Sheet Pertama)
        try {
            $data = Excel::toArray([], $file)[0];
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        // 3. Hapus Baris Header (baris pertama)
        array_shift($data);

        $importedCount = 0;
        $errors        = [];

        // 4. Loop Setiap Baris Data
        foreach ($data as $rowIndex => $row) {
            try {
                // Konversi huruf kolom (A, B, C, D) ke index array (0, 1, 2, 3)
                $codeIndex  = ord(strtoupper($mapping['item_code'])) - 65;
                $nameIndex  = ord(strtoupper($mapping['item_name'])) - 65;
                $stockIndex = ord(strtoupper($mapping['stock'])) - 65;
                $deptIndex  = ord(strtoupper($mapping['department'])) - 65;

                $itemCode = trim($row[$codeIndex] ?? '');
                $itemName = trim($row[$nameIndex] ?? '');
                $stockQty = (int) ($row[$stockIndex] ?? 0);
                $deptName = trim($row[$deptIndex] ?? '');

                // Skip jika kode item kosong (baris kosong)
                if (empty($itemCode)) {
                    continue;
                }

                // 5. Cari atau Buat Item Baru
                $item = Item::firstOrCreate(
                    ['item_code' => $itemCode],
                    ['item_name' => $itemName]
                );

                // Update nama item jika kosong di database tapi ada di Excel
                if (empty($item->item_name) && !empty($itemName)) {
                    $item->update(['item_name' => $itemName]);
                }

                // 6. Cari Departemen berdasarkan nama (case-insensitive)
                $department = Department::where('name', 'LIKE', '%' . $deptName . '%')->first();

                if (!$department) {
                    $errors[] = "Baris " . ($rowIndex + 2) . ": Departemen '{$deptName}' tidak ditemukan";
                    continue;
                }

                // 7. Update atau Buat Stok
                Stock::updateOrCreate(
                    [
                        'item_id'       => $item->id,
                        'department_id' => $department->id,
                    ],
                    [
                        'stock'      => $stockQty,
                        'updated_at' => now(),
                    ]
                );

                $importedCount++;

            } catch (\Exception $e) {
                $errors[] = "Baris " . ($rowIndex + 2) . ": " . $e->getMessage();
            }
        }

        // 8. Redirect dengan notifikasi
        if (count($errors) > 0) {
            $errorMsg = implode(' | ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $errorMsg .= ' ... dan ' . (count($errors) - 3) . ' error lainnya.';
            }
            return redirect()->back()->with('warning', "Berhasil import {$importedCount} data. Error: {$errorMsg}");
        }

        return redirect()->back()->with('success', "Berhasil import {$importedCount} data stok!");
    }
}