<?php

namespace App\Http\Controllers;

use App\Exports\StockUsageExport;
use App\Models\Department;
use App\Models\DepartmentStock;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Item;
use App\Models\StockLog;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InventoryStockController extends Controller
{
    /* ═══════════════════════════════════════════════════════════════
       INDEX
    ═══════════════════════════════════════════════════════════════ */
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = DepartmentStock::with(['department', 'item']);

        if (!$user->hasRole('Super Admin')) {
            $query->where('department_id', $user->department_id);
            $departments = collect([$user->department]);
        } else {
            if ($request->has('department_id') && $request->department_id != '') {
                $query->where('department_id', $request->department_id);
            }
            $departments = Department::all();
        }

        $stocks = $query->paginate(15);

        return view('inventory.stock.index', compact('stocks', 'departments'));
    }

    /* ═══════════════════════════════════════════════════════════════
       IMPORT EXCEL — Custom Mapping (Super Admin only)
       🔍 VERSI DEBUG
    ═══════════════════════════════════════════════════════════════ */
    public function importProcess(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'excel_file'         => 'required|mimes:xlsx,xls,csv|max:5120',
            'mapping'            => 'required|array',
        ]);

        $file    = $request->file('excel_file');
        $mapping = $request->input('mapping');

        // 2. Baca File Excel
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet   = $spreadsheet->getActiveSheet();
            $rows        = $worksheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        // ═══════════════════════════════════════════════════════════════
        // 🔍 DEBUG SECTION — TAMPILKAN ISI FILE & MAPPING
        // ═══════════════════════════════════════════════════════════════
        $codeIndex  = ord(strtoupper($mapping['item_code'] ?? 'A')) - 65;
        $nameIndex  = ord(strtoupper($mapping['item_name'] ?? 'B')) - 65;
        $stockIndex = ord(strtoupper($mapping['stock'] ?? 'C')) - 65;
        $deptIndex  = ord(strtoupper($mapping['department'] ?? 'D')) - 65;

        // Susun preview data yang akan di-import (10 baris pertama)
        $preview = [];
        foreach (array_slice($rows, 0, 10) as $i => $row) {
            $preview[] = [
                'baris'      => $i + 1,
                'raw_row'    => $row,
                'item_code'  => $row[$codeIndex] ?? '(kosong)',
                'item_name'  => $row[$nameIndex] ?? '(kosong)',
                'stock'      => $row[$stockIndex] ?? '(kosong)',
                'department' => $row[$deptIndex] ?? '(kosong)',
            ];
        }

        dd([
            '📋 MAPPING DARI FRONTEND' => $mapping,
            '🔢 INDEX YANG DIBACA' => [
                'item_code'  => "Huruf '{$mapping['item_code']}' → index array [{$codeIndex}]",
                'item_name'  => "Huruf '{$mapping['item_name']}' → index array [{$nameIndex}]",
                'stock'      => "Huruf '{$mapping['stock']}' → index array [{$stockIndex}]",
                'department' => "Huruf '{$mapping['department']}' → index array [{$deptIndex}]",
            ],
            '📊 TOTAL BARIS' => count($rows),
            '📄 10 BARIS PERTAMA FILE EXCEL' => $preview,
            '💾 DAFTAR DEPARTEMEN DI DATABASE' => Department::pluck('name', 'id')->toArray(),
        ]);
        // ═══════════════════════════════════════════════════════════════
        // 🔍 END DEBUG — Kode di bawah ini TIDAK AKAN DIJALANKAN saat debug
        // ═══════════════════════════════════════════════════════════════

        // 3. Hapus Baris Header (baris pertama)
        array_shift($rows);

        $importedCount = 0;
        $errors        = [];

        // 4. Loop Setiap Baris Data
        foreach ($rows as $rowIndex => $row) {
            try {
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
                DepartmentStock::updateOrCreate(
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

    /* ═══════════════════════════════════════════════════════════════
       CREATE — Form tambah stok (Super Admin only)
    ═══════════════════════════════════════════════════════════════ */
    public function create()
    {
        $departments = Department::all();
        $items       = Item::all();
        return view('inventory.stock.create', compact('departments', 'items'));
    }

    /* ═══════════════════════════════════════════════════════════════
       STORE — Simpan / tambah stok
    ═══════════════════════════════════════════════════════════════ */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'item_id'       => 'required|exists:items,id',
            'stock'         => 'required|numeric|min:0',
        ]);

        $departmentStock = DepartmentStock::where('department_id', $validated['department_id'])
            ->where('item_id', $validated['item_id'])
            ->first();

        if ($departmentStock) {
            $departmentStock->stock += $validated['stock'];
            $departmentStock->save();
        } else {
            DepartmentStock::create([
                'department_id' => $validated['department_id'],
                'item_id'       => $validated['item_id'],
                'stock'         => $validated['stock'],
            ]);
        }

        return redirect()->route('inventory.stock.index')
            ->with('success', 'Stok departemen berhasil ditambahkan.');
    }

    /* ═══════════════════════════════════════════════════════════════
       USE — Form pemakaian barang
    ═══════════════════════════════════════════════════════════════ */
    public function useItemForm($id)
    {
        $stock = DepartmentStock::with(['department', 'item'])->findOrFail($id);

        if (!auth()->user()->hasRole('Super Admin') && $stock->department_id !== auth()->user()->department_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('inventory.stock.use', compact('stock'));
    }

    public function processUseItem(Request $request, $id)
    {
        $validated = $request->validate([
            'used_stock' => 'required|numeric|min:1',
        ]);

        $stock = DepartmentStock::findOrFail($id);

        if ($stock->stock < $validated['used_stock']) {
            return back()
                ->withErrors(['used_stock' => 'Jumlah pemakaian melebihi sisa stok yang ada!'])
                ->withInput();
        }

        $stock->stock -= $validated['used_stock'];
        $stock->save();

        StockLog::create([
            'department_id' => $stock->department_id,
            'item_id'       => $stock->item_id,
            'quantity'      => $validated['used_stock'],
            'user_id'       => auth()->id(),
        ]);

        return redirect()->route('inventory.stock.index')
            ->with('success', 'Stok berhasil dikurangi dan dicatat dalam sistem.');
    }

    /* ═══════════════════════════════════════════════════════════════
       EXPORT USAGE
    ═══════════════════════════════════════════════════════════════ */
    public function exportUsage(Request $request)
    {
        $request->validate([
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date',   now()->format('Y-m-d'));

        $departmentId = auth()->user()->hasRole('Super Admin')
            ? $request->input('department_id')
            : auth()->user()->department_id;

        $filename = 'stock-usage-' . $startDate . '_to_' . $endDate . '.xlsx';

        return Excel::download(
            new StockUsageExport($startDate, $endDate, $departmentId),
            $filename
        );
    }
}