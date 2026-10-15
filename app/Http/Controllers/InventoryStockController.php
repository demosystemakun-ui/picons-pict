<?php

namespace App\Http\Controllers;

use App\Exports\StockUsageExport;
use App\Models\Category;
use App\Models\Department;
use App\Models\DepartmentStock;
use App\Models\StockUsage;
use App\Models\Item;
use App\Models\StockLog;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;


class InventoryStockController extends Controller
{
    /* Skor minimum agar sebuah header dianggap cocok dengan field sistem */
    private const MIN_SCORE = 60;

    /*
     * Kamus alias nama kolom (sudah dalam bentuk ternormalisasi:
     * huruf kecil, tanpa simbol, spasi tunggal). Tambah sendiri kalau perlu.
     */

    private const FIELD_ALIASES = [
        'department' => [
            'departemen', 'department', 'dept', 'dep', 'unit kerja', 'divisi', 'division',
            'bagian', 'seksi', 'section', 'nama departemen', 'nama dept',
        ],
        'item_name' => [
            'nama barang item', 'nama barang', 'nama item', 'nama produk', 'nama material',
            'item name', 'item description', 'description', 'deskripsi', 'barang', 'brg',
            'item', 'produk', 'product', 'material',
        ],
        'item_code' => [
            'kode item', 'kode barang', 'kd item', 'kd brg', 'kode', 'sku', 'item code',
            'part number', 'part no', 'no barang', 'id barang', 'kode material', 'material code',
        ],
        'stock' => [
            'sisa stok', 'stok akhir', 'stok', 'stock', 'jumlah', 'qty on hand', 'qty',
            'quantity', 'on hand', 'saldo', 'sisa',
        ],
    ];

    /* ═══════════════════════════════════════════════════════════════
       INDEX — Tampilan Tabel Stok
    ═══════════════════════════════════════════════════════════════ */
public function index(Request $request)
{
    $user  = auth()->user();
    
    // Ambil parameter filter periode
    $periodType  = $request->get('period_type', 'daily');
    $periodValue = $request->get('period_value', now()->format('Y-m-d'));

    $query = DepartmentStock::with(['department', 'item']);

    // JIKA BUKAN SUPER ADMIN DAN BUKAN ADMIN (Sesuaikan role admin Anda jika ada, misal 'Admin' atau 'Warehouse')
    // Jika role Admin ingin melihat seluruh stok juga, cukup izinkan Super Admin & Admin melihat semua.
    if (!$user->hasRole('Super Admin') && !$user->hasRole('Admin')) {
        // Jika murni user biasa/staff departemen, batasi hanya departemennya sendiri:
        $query->where('department_id', $user->department_id);
        $departments = collect([$user->department]);
    } else {
        // Untuk Super Admin & Admin: bisa filter berdasarkan department_id atau lihat semua
        if ($request->has('department_id') && $request->department_id != '') {
            $query->where('department_id', $request->department_id);
        }
        $departments = Department::all();
    }

    // Menghitung jumlah terpakai (used_stock) secara real-time berdasarkan filter periode
    $query->withSum(['usages as used_stock' => function ($q) use ($periodType, $periodValue) {
        if ($periodType == 'monthly') {
            $q->whereYear('created_at', substr($periodValue, 0, 4))
              ->whereMonth('created_at', substr($periodValue, 5, 2));
        } elseif ($periodType == 'yearly') {
            $q->whereYear('created_at', $periodValue);
        } else {
            $q->whereDate('created_at', $periodValue);
        }
    }], 'quantity_used');

    $stocks = $query->orderBy('updated_at', 'desc')->paginate(15)->appends($request->all());

    $categories = Category::all();
    $units      = Unit::all();

    return view('inventory.stock.index', compact('stocks', 'departments', 'categories', 'units'));
}

    /* ═══════════════════════════════════════════════════════════════
       IMPORT STEP 1 — Analisa file (AJAX)
       Mengembalikan: daftar sheet, baris header, daftar kolom,
       tebakan mapping otomatis, dan preview beberapa baris data.
    ═══════════════════════════════════════════════════════════════ */
    public function importAnalyze(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
            'sheet'      => 'nullable|integer|min:0',
            'header_row' => 'nullable|integer|min:1',
        ]);

        try {
            [$sheetNames, $sheetIndex, $rows] = $this->loadSheet(
                $request->file('excel_file')->getRealPath(),
                (int) $request->input('sheet', 0)
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal membaca file: ' . $e->getMessage()], 422);
        }

        $rows = array_values($rows);
        if (count($rows) === 0) {
            return response()->json(['message' => 'Sheet ini kosong.'], 422);
        }

        $headerIdx = $request->filled('header_row')
            ? (int) $request->input('header_row') - 1
            : $this->detectHeaderRow($rows);
        $headerIdx = max(0, min($headerIdx, count($rows) - 1));

        $columns = [];
        $labels  = [];
        foreach ($rows[$headerIdx] as $i => $cell) {
            $label = $this->cleanText($cell);
            if ($label === '') {
                continue;
            }
            $labels[$i] = $label;
            $columns[]  = [
                'index'  => $i,
                'letter' => Coordinate::stringFromColumnIndex($i + 1),
                'label'  => $label,
            ];
        }

        $preview = [];
        foreach (array_slice($rows, $headerIdx + 1, 5) as $r) {
            $line = [];
            foreach ($labels as $i => $_) {
                $line[] = mb_strimwidth($this->cleanText($r[$i] ?? null), 0, 40, '…');
            }
            $preview[] = $line;
        }

        return response()->json([
            'sheets'        => $sheetNames,
            'sheet'         => $sheetIndex,
            'header_row'    => $headerIdx + 1,
            'columns'       => $columns,
            'suggested'     => (object) $this->suggestMapping($labels),
            'preview_heads' => array_values($labels),
            'preview'       => $preview,
            'total_rows'    => max(0, count($rows) - $headerIdx - 1),
        ]);
    }
    
    public function processUseItem(Request $request, $id)
{
    $validated = $request->validate([
        'used_stock' => 'required|numeric|min:1',
    ]);

    $stock = DepartmentStock::findOrFail($id);

    if ($stock->stock < $validated['used_stock']) {
        return back()->with('error', 'Jumlah pemakaian melebihi sisa stok yang tersedia!');
    }

    // Kurangi stok utama
    $stock->stock -= $validated['used_stock'];
    $stock->save();

    // Catat ke log pemakaian (StockUsage)
    StockUsage::create([
        'department_stock_id' => $stock->id,
        'user_id'             => auth()->id(),
        'quantity_used'       => $validated['used_stock'],
    ]);

    return redirect()->route('inventory.stock.index')
        ->with('success', 'Stok berhasil dikurangi dan dicatat dalam sistem.');
}
    /* ═══════════════════════════════════════════════════════════════
       IMPORT STEP 2 — Proses import berdasarkan mapping
    ═══════════════════════════════════════════════════════════════ */
    public function importProcess(Request $request)
    {
        $request->validate([
            'excel_file'          => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
            'sheet'               => 'nullable|integer|min:0',
            'header_row'          => 'required|integer|min:1',
            'mapping'             => 'required|array',
            'mapping.department'  => 'required|integer|min:0',
            'mapping.item_name'   => 'required|integer|min:0',
            'mapping.stock'       => 'required|integer|min:0',
            'mapping.item_code'   => 'nullable|integer|min:0',
            'default_category_id' => 'nullable|integer|exists:categories,id',
            'default_unit_id'     => 'nullable|integer|exists:units,id',
        ], [
            'excel_file.required'         => 'File Excel belum dipilih.',
            'excel_file.mimes'            => 'Format file harus .xlsx, .xls, atau .csv.',
            'excel_file.max'              => 'Ukuran file maksimal 5MB.',
            'excel_file.uploaded'         => 'Upload gagal (kemungkinan melebihi batas upload_max_filesize di PHP).',
            'mapping.department.required' => 'Kolom Departemen belum dipilih.',
            'mapping.item_name.required'  => 'Kolom Nama Barang belum dipilih.',
            'mapping.stock.required'      => 'Kolom Sisa Stok belum dipilih.',
        ]);

        try {
            [, , $rows] = $this->loadSheet(
                $request->file('excel_file')->getRealPath(),
                (int) $request->input('sheet', 0)
            );
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        $map       = $request->input('mapping');
        $deptCol   = (int) $map['department'];
        $nameCol   = (int) $map['item_name'];
        $stockCol  = (int) $map['stock'];
        $codeCol   = (isset($map['item_code']) && $map['item_code'] !== '') ? (int) $map['item_code'] : null;
        $headerRow = (int) $request->input('header_row'); // 1-based

        $rows     = array_values($rows);
        $dataRows = array_slice($rows, $headerRow); // semua baris setelah header

        // Default untuk item baru (hanya dipakai jika kolomnya ada di tabel items)
        $defaultCategoryId = $request->input('default_category_id');
        $defaultUnitId     = $request->input('default_unit_id');
        $needCategory      = Schema::hasColumn('items', 'category_id');
        $needUnit          = Schema::hasColumn('items', 'unit_id');

        $imported  = 0;
        $newItems  = 0;
        $newDepts  = 0;
        $filled    = 0;
        $skipped   = [];
        $deptCache = [];
        $lastDept  = null;

        foreach ($dataRows as $offset => $row) {
            $lineNum  = $headerRow + $offset + 1; // nomor baris asli di Excel
            $deptName = $this->cleanText($row[$deptCol] ?? null);
            $itemName = $this->cleanText($row[$nameCol] ?? null);
            $itemCode = $codeCol !== null ? $this->cleanText($row[$codeCol] ?? null) : '';
            $rawStock = $row[$stockCol] ?? null;

            // Baris benar-benar kosong → abaikan tanpa laporan
            if ($deptName === '' && $itemName === '' && $itemCode === ''
                && ($rawStock === null || trim((string) $rawStock) === '')) {
                continue;
            }

            // Departemen kosong → ikuti baris di atasnya (kasus sel di-merge)
            if ($deptName === '') {
                if ($lastDept === null) {
                    $skipped[] = "Baris {$lineNum}: departemen kosong";
                    continue;
                }
                $deptName = $lastDept;
                $filled++;
            } else {
                $lastDept = $deptName;
            }

            if ($itemName === '' && $itemCode === '') {
                $skipped[] = "Baris {$lineNum}: nama dan kode barang kosong";
                continue;
            }

            $qty = $this->parseQty($rawStock);
            if ($qty === null) {
                $skipped[] = "Baris {$lineNum}: stok tidak valid (\"" . $this->cleanText($rawStock) . "\")";
                continue;
            }
            if ($qty < 0) {
                $skipped[] = "Baris {$lineNum}: stok negatif ({$qty})";
                continue;
            }

            try {
                // ── Item ──
                $item = null;
                if ($itemCode !== '') {
                    $item = Item::where('item_code', $itemCode)->first();
                } elseif ($itemName !== '') {
                    $item = Item::where('item_name', $itemName)->first();
                }

                if (!$item) {
                    if ($itemCode === '') {
                        $skipped[] = "Baris {$lineNum}: kode kosong dan barang \"{$itemName}\" belum terdaftar";
                        continue;
                    }
                    if ($needCategory && !$defaultCategoryId) {
                        $skipped[] = "Baris {$lineNum}: item baru \"{$itemCode}\" butuh kategori, pilih \"Kategori default\" di form import";
                        continue;
                    }
                    if ($needUnit && !$defaultUnitId) {
                        $skipped[] = "Baris {$lineNum}: item baru \"{$itemCode}\" butuh satuan, pilih \"Satuan default\" di form import";
                        continue;
                    }

                    $attrs = [
                        'item_code'     => $itemCode,
                        'item_name'     => $itemName !== '' ? $itemName : $itemCode,
                        'current_stock' => 0,
                        'minimum_stock' => 0,
                    ];
                    if ($needCategory) $attrs['category_id'] = $defaultCategoryId;
                    if ($needUnit)     $attrs['unit_id']     = $defaultUnitId;

                    $item = new Item();
                    $item->forceFill($attrs)->save();
                    $newItems++;
                } elseif ($itemName !== '' && $item->item_name !== $itemName) {
                    $item->update(['item_name' => $itemName]);
                }

                // ── Departemen ──
                $department = $this->resolveDepartment($deptName, $deptCache, $newDepts);

                // ── Stok (atribut di-set langsung, tidak tergantung $fillable) ──
                $ds = DepartmentStock::where('item_id', $item->id)
                    ->where('department_id', $department->id)
                    ->first() ?? new DepartmentStock();

                $ds->item_id       = $item->id;
                $ds->department_id = $department->id;
                $ds->stock         = $qty;
                $ds->updated_at    = now();
                $ds->save();

                $imported++;
            } catch (\Throwable $e) {
                $skipped[] = "Baris {$lineNum}: error - " . $e->getMessage();
            }
        }

        Log::info('Stock import selesai', [
            'user_id'  => auth()->id(),
            'imported' => $imported,
            'skipped'  => count($skipped),
            'detail'   => $skipped,
        ]);

        // ── Pesan hasil ──
        $msg = "Berhasil import {$imported} data stok.";
        if ($newItems > 0) $msg .= " {$newItems} item baru dibuat.";
        if ($newDepts > 0) $msg .= " {$newDepts} departemen baru dibuat.";
        if ($filled > 0)   $msg .= " {$filled} baris memakai departemen dari baris di atasnya.";

        $redirect = redirect()->back()->with('import_report', array_slice($skipped, 0, 30));

        if ($imported === 0) {
            return $redirect->with('warning', 'Tidak ada data yang masuk. Cek kolom mapping dan alasan di bawah.');
        }
        if (count($skipped) > 0) {
            $msg .= ' ' . count($skipped) . ' baris dilewati.';
            return $redirect->with('warning', $msg);
        }

        return $redirect->with('success', $msg);
    }

    /* ═══════════════════════════════════════════════════════════════
       HELPER IMPORT
    ═══════════════════════════════════════════════════════════════ */

    /** Baca satu sheet → [namaSheet[], indexSheet, rows[][]] */
    private function loadSheet(string $path, int $sheetIndex = 0): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);

        $names = $spreadsheet->getSheetNames();
        if ($sheetIndex < 0 || $sheetIndex >= count($names)) {
            $sheetIndex = 0;
        }

        // calculateFormulas=true, formatData=false (nilai mentah, angka tetap angka)
        $rows = $spreadsheet->getSheet($sheetIndex)->toArray(null, true, false, false);
        $spreadsheet->disconnectWorksheets();

        return [$names, $sheetIndex, $rows];
    }

    /** Cari baris header: baris (dari 20 teratas) yang paling banyak cocok dengan alias */
    private function detectHeaderRow(array $rows): int
    {
        $best      = 0;
        $bestScore = -1;

        foreach (array_slice($rows, 0, 20, true) as $i => $row) {
            $matches = 0;
            $strings = 0;
            foreach ($row as $cell) {
                if (!is_string($cell) || trim($cell) === '') {
                    continue;
                }
                $strings++;
                $norm = $this->normalize($cell);
                foreach (self::FIELD_ALIASES as $aliases) {
                    if ($this->scoreHeader($norm, $aliases) >= self::MIN_SCORE) {
                        $matches++;
                        break;
                    }
                }
            }
            $score = $matches * 10 + min($strings, 10);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best      = $i;
            }
        }

        return $best;
    }

    /** Tebak mapping: field => ['column' => index, 'score' => skor] (1 kolom hanya untuk 1 field) */
    private function suggestMapping(array $labels): array
    {
        $candidates = [];
        foreach ($labels as $col => $label) {
            $norm = $this->normalize($label);
            if ($norm === '') {
                continue;
            }
            foreach (self::FIELD_ALIASES as $field => $aliases) {
                $score = $this->scoreHeader($norm, $aliases);
                if ($score >= self::MIN_SCORE) {
                    $candidates[] = [$score, $col, $field];
                }
            }
        }

        usort($candidates, fn ($a, $b) => $b[0] <=> $a[0]);

        $map  = [];
        $used = [];
        foreach ($candidates as [$score, $col, $field]) {
            if (isset($map[$field]) || isset($used[$col])) {
                continue;
            }
            $map[$field] = ['column' => $col, 'score' => $score];
            $used[$col]  = true;
        }

        return $map;
    }

    /** 100 = sama persis; 63-80 = mengandung alias; <=85 = mirip (typo) */
    private function scoreHeader(string $norm, array $aliases): int
    {
        if ($norm === '') {
            return 0;
        }
        $best = 0;
        foreach ($aliases as $alias) {
            if ($norm === $alias) {
                return 100;
            }
            if (preg_match('/\b' . preg_quote($alias, '/') . '\b/u', $norm)) {
                $best = max($best, 60 + min(mb_strlen($alias), 20));
            }
            similar_text($norm, $alias, $pct);
            if ($pct >= 80) {
                $best = max($best, (int) ($pct * 0.85));
            }
        }
        return $best;
    }

    private function normalize(?string $v): string
    {
        $v = mb_strtolower((string) $v);
        $v = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $v);
        return trim(preg_replace('/\s+/', ' ', $v));
    }

    private function cleanText($v): string
    {
        if ($v === null || !is_scalar($v)) {
            return '';
        }
        return trim(preg_replace('/\s+/u', ' ', (string) $v));
    }

    /** "12", 12.0, "12 Unit", "1.250" → int. Tidak ada angka → null */
    private function parseQty($v): ?int
    {
        if ($v === null) {
            return null;
        }
        if (is_int($v) || is_float($v)) {
            return (int) round($v);
        }
        $s = trim((string) $v);
        if ($s === '' || !preg_match('/-?\d+(?:[.,]\d+)*/', $s, $m)) {
            return null;
        }
        $num = $m[0];
        if (preg_match('/^-?\d{1,3}([.,]\d{3})+$/', $num)) {
            $num = str_replace(['.', ','], '', $num);   // pemisah ribuan
        } else {
            $num = str_replace(',', '.', $num);         // desimal
        }
        return (int) round((float) $num);
    }

    /** Cari departemen by nama atau kode (tidak sensitif huruf), buat baru jika belum ada */
    private function resolveDepartment(string $name, array &$cache, int &$created): Department
    {
        $key = mb_strtolower($name);
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $dept = Department::whereRaw('LOWER(name) = ?', [$key])
            ->orWhereRaw('LOWER(code) = ?', [$key])
            ->first();

        if (!$dept) {
            $dept = new Department();
            $dept->forceFill([
                'name'        => $name,
                'code'        => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3)),
                'cost_center' => 'CC-' . str_pad(Department::count() + 1, 3, '0', STR_PAD_LEFT),
                'is_active'   => true,
            ])->save();
            $created++;
        }

        return $cache[$key] = $dept;
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