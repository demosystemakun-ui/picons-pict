<?php

namespace App\Http\Controllers;

use App\Exports\StockUsageExport;
use App\Models\Department;
use App\Models\DepartmentStock;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Item;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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

    public function importProcess(Request $request)
{
    $request->validate([
        'excel_file' => 'required|mimes:xlsx,xls,csv|max:5120',
        'mapping' => 'required|array',
    ]);

    $file = $request->file('excel_file');
    $mapping = $request->input('mapping'); // Array mapping dari frontend

    // Baca file Excel menggunakan Maatwebsite Excel
    $data = Excel::toArray(new \stdClass, $file)[0]; // Ambil sheet pertama

    // Hapus baris header (baris pertama)
    $header = array_shift($data);

    $importedCount = 0;
    $errors = [];

    foreach ($data as $rowIndex => $row) {
        try {
            // Ambil data berdasarkan mapping yang dipilih user
            // Contoh: $mapping['item_code'] bernilai 'A', maka ambil index 0 (A=0, B=1, dst)
            $itemCode = $row[ord($mapping['item_code']) - 65] ?? null;
            $itemName = $row[ord($mapping['item_name']) - 65] ?? null;
            $stockQty = $row[ord($mapping['stock']) - 65] ?? 0;
            $deptName = $row[ord($mapping['department']) - 65] ?? null;

            // Skip jika kode item kosong
            if (empty($itemCode)) continue;

            // Cari atau buat Item baru
            $item = Item::firstOrCreate(
                ['item_code' => $itemCode],
                ['item_name' => $itemName]
            );

            // Cari Departemen berdasarkan nama
            $department = Department::where('name', 'LIKE', '%' . $deptName . '%')->first();
            if (!$department) {
                $errors[] = "Baris " . ($rowIndex + 2) . ": Departemen '$deptName' tidak ditemukan.";
                continue;
            }

            // Update atau Buat Stok
            Stock::updateOrCreate(
                [
                    'item_id' => $item->id,
                    'department_id' => $department->id,
                ],
                [
                    'stock' => $stockQty,
                    'updated_at' => now(),
                ]
            );

            $importedCount++;

        } catch (\Exception $e) {
            $errors[] = "Baris " . ($rowIndex + 2) . ": " . $e->getMessage();
        }
    }

    // Redirect dengan pesan sukses/gagal
    if (count($errors) > 0) {
        return redirect()->back()->with('warning', "Berhasil import $importedCount data, tapi ada beberapa error: " . implode(', ', array_slice($errors, 0, 3)) . "...");
    }

    return redirect()->back()->with('success', "Berhasil import $importedCount data stok!");
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

        // User biasa hanya bisa akses stok departemennya sendiri
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

        // Kurangi stok utama
        $stock->stock -= $validated['used_stock'];
        $stock->save();

        // Catat riwayat pemakaian
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
       EXPORT USAGE — Export Excel pemakaian stok harian
    ═══════════════════════════════════════════════════════════════ */
    public function exportUsage(Request $request)
    {
        $request->validate([
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        // Default range: awal bulan ini → hari ini
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date',   now()->format('Y-m-d'));

        // Non Super Admin → paksa filter departemennya sendiri
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