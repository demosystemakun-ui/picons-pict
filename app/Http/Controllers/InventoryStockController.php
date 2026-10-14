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
       INDEX — Tampilan Tabel Stok
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

        $stocks = $query->orderBy('updated_at', 'desc')->paginate(15);

        return view('inventory.stock.index', compact('stocks', 'departments'));
    }

    /* ═══════════════════════════════════════════════════════════════
       IMPORT PROCESS — Import via Modal dengan Custom Mapping
       (Auto-create departemen & item jika belum ada)
    ═══════════════════════════════════════════════════════════════ */
    public function importProcess(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'excel_file'         => 'required|mimes:xlsx,xls,csv|max:5120',
            'mapping'            => 'required|array',
            'mapping.item_code'  => 'required|string',
            'mapping.item_name'  => 'required|string',
            'mapping.stock'      => 'required|string',
            'mapping.department' => 'required|string',
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

        // 3. Hapus Baris Header
        array_shift($rows);

        // 4. Konversi huruf kolom ke index (A=0, B=1, C=2, dst)
        $codeIndex  = ord(strtoupper($mapping['item_code'])) - 65;
        $nameIndex  = ord(strtoupper($mapping['item_name'])) - 65;
        $stockIndex = ord(strtoupper($mapping['stock'])) - 65;
        $deptIndex  = ord(strtoupper($mapping['department'])) - 65;

        $importedCount = 0;
        $newItemsCount = 0;
        $newDeptsCount = 0;
        $errors        = [];

        // 5. Loop Setiap Baris
        foreach ($rows as $rowIndex => $row) {
            $lineNum = $rowIndex + 2;

            try {
                $itemCode = trim($row[$codeIndex] ?? '');
                $itemName = trim($row[$nameIndex] ?? '');
                $stockQty = (int) ($row[$stockIndex] ?? 0);
                $deptName = trim($row[$deptIndex] ?? '');

                // Skip baris kosong
                if (empty($itemCode) || empty($itemName) || empty($deptName)) {
                    continue;
                }

                // ─────────────────────────────────────────
                // 6. Cari atau Buat Item
                // ─────────────────────────────────────────
                $item = Item::where('item_code', $itemCode)->first();
                if (!$item) {
                    $item = Item::create([
                        'item_code'     => $itemCode,
                        'item_name'     => $itemName,
                        'current_stock' => 0,
                        'minimum_stock' => 0,
                    ]);
                    $newItemsCount++;
                } else {
                    if (!empty($itemName) && $item->item_name !== $itemName) {
                        $item->update(['item_name' => $itemName]);
                    }
                }

                // ─────────────────────────────────────────
                // 7. Cari atau Buat Departemen (AUTO-CREATE)
                // ─────────────────────────────────────────
                $department = Department::where('name', 'LIKE', $deptName)->first();

                if (!$department) {
                    $department = Department::create([
                        'name'        => $deptName,
                        'code'        => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $deptName), 0, 3)),
                        'cost_center' => 'CC-' . str_pad(Department::count() + 1, 3, '0', STR_PAD_LEFT),
                        'is_active'   => true,
                    ]);
                    $newDeptsCount++;
                }

                // ─────────────────────────────────────────
                // 8. Update atau Buat Stok
                // ─────────────────────────────────────────
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
                $errors[] = "Baris {$lineNum}: " . $e->getMessage();
            }
        }

        // 9. Susun pesan sukses
        $successMsg = "Berhasil import {$importedCount} data stok!";
        if ($newItemsCount > 0) {
            $successMsg .= " ({$newItemsCount} item baru)";
        }
        if ($newDeptsCount > 0) {
            $successMsg .= " ({$newDeptsCount} departemen baru otomatis dibuat)";
        }

        // 10. Redirect dengan notifikasi
        if (count($errors) > 0) {
            $errorMsg = implode(' | ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $errorMsg .= ' ... dan ' . (count($errors) - 3) . ' error lainnya.';
            }
            return redirect()->back()->with('warning', $successMsg . " Error: {$errorMsg}");
        }

        return redirect()->back()->with('success', $successMsg);
    }

    /* ═══════════════════════════════════════════════════════════════
       IMPORT LANGSUNG — Bypass UI untuk Debugging
       Cara pakai:
       1. Copy file Excel ke: storage/app/data-stok.xlsx
       2. Akses: /inventory/stock/import-langsung
       3. Akan muncul response JSON berisi hasil import
    ═══════════════════════════════════════════════════════════════ */
    public function importLangsung()
    {
        // Path file Excel yang akan dibaca
        $filePath = storage_path('app/data-stok.xlsx');

        // Cek file ada atau tidak
        if (!file_exists($filePath)) {
            return response()->json([
                '❌ ERROR' => 'File Excel tidak ditemukan!',
                'path_yang_dicari' => $filePath,
                'solusi' => 'Copy file Excel kamu ke path di atas, rename menjadi "data-stok.xlsx"',
            ], 404);
        }

        // Baca Excel
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet   = $spreadsheet->getActiveSheet();
            $rows        = $worksheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            return response()->json([
                '❌ ERROR BACA EXCEL' => $e->getMessage(),
            ], 500);
        }

        // Hapus header
        array_shift($rows);

        // FIXED MAPPING: A=departemen, B=nama, C=kode, D=stock
        $deptIndex  = 0; // Kolom A
        $nameIndex  = 1; // Kolom B
        $codeIndex  = 2; // Kolom C
        $stockIndex = 3; // Kolom D

        $importedCount = 0;
        $log           = [];

        foreach ($rows as $rowIndex => $row) {
            $lineNum = $rowIndex + 2;

            try {
                $deptName = trim($row[$deptIndex] ?? '');
                $itemName = trim($row[$nameIndex] ?? '');
                $itemCode = trim($row[$codeIndex] ?? '');
                $stockQty = (int) ($row[$stockIndex] ?? 0);

                // Skip jika kosong
                if (empty($itemCode) || empty($deptName) || empty($itemName)) {
                    $log[] = "Baris {$lineNum}: ⏭️ DILEWATI (kolom kosong)";
                    continue;
                }

                // Cari/Buat Item
                $item = Item::where('item_code', $itemCode)->first();
                if (!$item) {
                    $item = Item::create([
                        'item_code'     => $itemCode,
                        'item_name'     => $itemName,
                        'current_stock' => 0,
                        'minimum_stock' => 0,
                    ]);
                    $log[] = "Baris {$lineNum}: 🆕 Item dibuat - {$itemCode}";
                } else {
                    $log[] = "Baris {$lineNum}: ✅ Item ada - {$itemCode}";
                }

                // Cari/Buat Departemen
                $department = Department::where('name', $deptName)->first();
                if (!$department) {
                    $department = Department::create([
                        'name'        => $deptName,
                        'code'        => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $deptName), 0, 3)),
                        'cost_center' => 'CC-' . str_pad(Department::count() + 1, 3, '0', STR_PAD_LEFT),
                        'is_active'   => true,
                    ]);
                    $log[] = "Baris {$lineNum}: 🆕 Departemen dibuat - {$deptName}";
                } else {
                    $log[] = "Baris {$lineNum}: ✅ Departemen ada - {$deptName}";
                }

                // Simpan Stok
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

                $log[] = "Baris {$lineNum}: 💾 STOK DISIMPAN - {$itemCode} di {$deptName} = {$stockQty}";
                $importedCount++;

            } catch (\Exception $e) {
                $log[] = "Baris {$lineNum}: ❌ ERROR - " . $e->getMessage();
            }
        }

        return response()->json([
            '✅ STATUS'              => 'IMPORT SELESAI',
            '📊 TOTAL BARIS'         => count($rows),
            '✅ BERHASIL'            => $importedCount,
            '📋 LOG DETAIL'          => $log,
            '💾 DEPARTEMEN DI DB'    => Department::pluck('name')->toArray(),
            '📦 TOTAL ITEM DI DB'    => Item::count(),
            '📦 TOTAL STOK DI DB'    => DepartmentStock::count(),
        ]);
    }

    /* ═══════════════════════════════════════════════════════════════
       CREATE — Form tambah stok manual
    ═══════════════════════════════════════════════════════════════ */
    public function create()
    {
        $departments = Department::all();
        $items       = Item::all();
        return view('inventory.stock.create', compact('departments', 'items'));
    }

    /* ═══════════════════════════════════════════════════════════════
       STORE — Simpan stok manual
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
       EXPORT USAGE — Export Excel pemakaian stok
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