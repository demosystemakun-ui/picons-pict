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

        // 3. Hapus Baris Header (baris pertama)
        array_shift($rows);

        // 4. Konversi huruf kolom (A, B, C, D) ke index array (0, 1, 2, 3)
        $codeIndex  = ord(strtoupper($mapping['item_code'])) - 65;
        $nameIndex  = ord(strtoupper($mapping['item_name'])) - 65;
        $stockIndex = ord(strtoupper($mapping['stock'])) - 65;
        $deptIndex  = ord(strtoupper($mapping['department'])) - 65;

        $importedCount = 0;
        $errors        = [];

        // 5. Loop Setiap Baris Data
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

                // 6. Cari atau Buat Item Baru
                $item = Item::firstOrCreate(
                    ['item_code' => $itemCode],
                    ['item_name' => $itemName]
                );

                if (empty($item->item_name) && !empty($itemName)) {
                    $item->update(['item_name' => $itemName]);
                }

                // 7. Cari Departemen berdasarkan nama (case-insensitive)
                $department = Department::where('name', 'LIKE', '%' . $deptName . '%')->first();

                if (!$department) {
                    $errors[] = "Baris " . ($rowIndex + 2) . ": Departemen '{$deptName}' tidak ditemukan";
                    continue;
                }

                // 8. Update atau Buat Stok
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

        // 9. Redirect dengan notifikasi
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