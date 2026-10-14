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
    // Hapus log lama biar fresh
    $logPath = storage_path('logs/import-debug.log');
    if (file_exists($logPath)) {
        unlink($logPath);
    }
    
    $log = function($msg) use ($logPath) {
        file_put_contents($logPath, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
    };
    
    // ═══════════════════════════════════════════════
    // STEP 1: Cek File Diterima
    // ═══════════════════════════════════════════════
    $log("========== IMPORT DIMULAI ==========");
    $log("Request Method: " . $request->method());
    $log("Has File: " . ($request->hasFile('excel_file') ? 'YA' : 'TIDAK'));
    $log("Total Files: " . count($request->allFiles()));
    $log("Total Input: " . count($request->all()));
    $log("Mapping: " . json_encode($request->input('mapping')));
    
    if ($request->hasFile('excel_file')) {
        $file = $request->file('excel_file');
        $log("File Name: " . $file->getClientOriginalName());
        $log("File Size: " . $file->getSize() . " bytes");
        $log("File Valid: " . ($file->isValid() ? 'YA' : 'TIDAK'));
        $log("File Error Code: " . $file->getError());
    } else {
        $log("❌ FILE TIDAK DITERIMA KE SERVER!");
        $log("Cek: post_max_size=" . ini_get('post_max_size'));
        $log("Cek: upload_max_filesize=" . ini_get('upload_max_filesize'));
        $log("Cek: file_uploads=" . ini_get('file_uploads'));
        
        return redirect()->back()->with('error', 'File tidak diterima server. Cek log di storage/logs/import-debug.log');
    }
    
    // ═══════════════════════════════════════════════
    // STEP 2: Baca Excel
    // ═══════════════════════════════════════════════
    try {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet   = $spreadsheet->getActiveSheet();
        $rows        = $worksheet->toArray(null, true, true, false);
        $log("✅ Excel terbaca. Total baris: " . count($rows));
    } catch (\Exception $e) {
        $log("❌ GAGAL BACA EXCEL: " . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal baca Excel: ' . $e->getMessage());
    }
    
    // ═══════════════════════════════════════════════
    // STEP 3: Hapus Header
    // ═══════════════════════════════════════════════
    array_shift($rows);
    $log("Setelah hapus header, baris data: " . count($rows));
    
    // ═══════════════════════════════════════════════
    // STEP 4: Hitung Index Kolom
    // ═══════════════════════════════════════════════
    $mapping    = $request->input('mapping');
    $codeIndex  = ord(strtoupper($mapping['item_code'])) - 65;
    $nameIndex  = ord(strtoupper($mapping['item_name'])) - 65;
    $stockIndex = ord(strtoupper($mapping['stock'])) - 65;
    $deptIndex  = ord(strtoupper($mapping['department'])) - 65;
    
    $log("Mapping: item_code={$mapping['item_code']}(idx {$codeIndex}), item_name={$mapping['item_name']}(idx {$nameIndex}), stock={$mapping['stock']}(idx {$stockIndex}), department={$mapping['department']}(idx {$deptIndex})");
    
    // ═══════════════════════════════════════════════
    // STEP 5: Loop Import
    // ═══════════════════════════════════════════════
    $importedCount = 0;
    $errors        = [];
    
    foreach ($rows as $rowIndex => $row) {
        $lineNum = $rowIndex + 2;
        
        try {
            $itemCode = trim($row[$codeIndex] ?? '');
            $itemName = trim($row[$nameIndex] ?? '');
            $stockQty = (int) ($row[$stockIndex] ?? 0);
            $deptName = trim($row[$deptIndex] ?? '');
            
            $log("--- Baris {$lineNum}: code='{$itemCode}', name='{$itemName}', qty={$stockQty}, dept='{$deptName}'");
            
            if (empty($itemCode) || empty($itemName) || empty($deptName)) {
                $log("   ⏭️  DILEWATI (ada kolom kosong)");
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
                $log("   🆕 Item baru dibuat: {$item->id} - {$itemCode}");
            } else {
                $log("   ✅ Item ditemukan: {$item->id} - {$itemCode}");
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
                $log("   🆕 Departemen baru dibuat: {$department->id} - {$deptName}");
            } else {
                $log("   ✅ Departemen ditemukan: {$department->id} - {$deptName}");
            }
            
            // Update/Buat Stok
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
            
            $log("   💾 STOK DISIMPAN: item_id={$item->id}, dept_id={$department->id}, stock={$stockQty}");
            $importedCount++;
            
        } catch (\Exception $e) {
            $log("   ❌ ERROR Baris {$lineNum}: " . $e->getMessage());
            $errors[] = "Baris {$lineNum}: " . $e->getMessage();
        }
    }
    
    $log("========== SELESAI ==========");
    $log("Total berhasil import: {$importedCount}");
    $log("Total error: " . count($errors));
    
    if (count($errors) > 0) {
        return redirect()->back()->with('warning', "Berhasil import {$importedCount} data. Ada error, cek log.");
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