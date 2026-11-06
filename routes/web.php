<?php

use App\Http\Controllers\Api\ItemLookupController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConsumableRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\InventoryStockController;
use App\Http\Controllers\ProcurementRequestController;
use App\Http\Controllers\ReimbursementController;
use App\Http\Controllers\SignerController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorComparisonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CashAdvanceController;
use App\Http\Controllers\LiquidationController;

/* ═══════════════════════════════════════════════════════════════════
   AUTHENTICATION
═══════════════════════════════════════════════════════════════════ */
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/* ═══════════════════════════════════════════════════════════════════
   AUTHENTICATED ROUTES
═══════════════════════════════════════════════════════════════════ */
Route::middleware(['auth'])->group(function () {

    /* ─────────────────────────────────────────────
       DASHBOARD
    ───────────────────────────────────────────── */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /* ─────────────────────────────────────────────
       MASTER DATA
    ───────────────────────────────────────────── */
    Route::resource('items', ItemController::class);
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('units', UnitController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('vendors', VendorController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::middleware(['role:Super Admin'])->group(function () {
        Route::resource('signers', SignerController::class)->except(['show', 'create']);
        Route::get('/users/nonaktif', [UserController::class, 'nonaktifIndex'])->name('users.nonaktif.index');
        Route::get('/users/{user}/nonaktif', [UserController::class, 'nonaktif'])->name('users.nonaktif');
        Route::resource('users', UserController::class)->except(['show']);
    });

    /* ─────────────────────────────────────────────
       CASH ADVANCE
    ───────────────────────────────────────────── */
    Route::resource('cash-advances', CashAdvanceController::class);
    Route::post('cash-advances/{cashAdvance}/approve', [CashAdvanceController::class, 'approve'])->name('cash-advances.approve');
    Route::get('cash-advances/{cashAdvance}/print', [CashAdvanceController::class, 'print'])->name('cash-advances.print');
    Route::get('cash-advances/{cashAdvance}/pdf', [CashAdvanceController::class, 'pdf'])->name('cash-advances.pdf');

    /* ─────────────────────────────────────────────
       LIQUIDATION
    ───────────────────────────────────────────── */
    Route::resource('liquidations', LiquidationController::class);
    Route::post('liquidations/{liquidation}/approve', [LiquidationController::class, 'approve'])->name('liquidations.approve');
    Route::get('liquidations/{liquidation}/print', [LiquidationController::class, 'print'])->name('liquidations.print');
    Route::get('liquidations/{liquidation}/pdf', [LiquidationController::class, 'pdf'])->name('liquidations.pdf');

    /* ─────────────────────────────────────────────
       INVENTORY STOCK
    ───────────────────────────────────────────── */
    Route::get('/inventory/stock', [InventoryStockController::class, 'index'])->name('inventory.stock.index');
    Route::get('/inventory/stock/export-usage', [InventoryStockController::class, 'exportUsage'])->name('inventory.stock.export-usage');
    Route::get('/inventory/stock/use', [InventoryStockController::class, 'useStockForm'])->name('inventory.stock.use');
    Route::get('/inventory/stock/items-by-department', [InventoryStockController::class, 'getItemsByDepartment'])->name('inventory.stock.items-by-department');

    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('/inventory/stock/create', [InventoryStockController::class, 'create'])->name('inventory.stock.create');
        Route::post('/inventory/stock', [InventoryStockController::class, 'store'])->name('inventory.stock.store');
        Route::post('/inventory/stock/import-analyze', [InventoryStockController::class, 'importAnalyze'])->name('inventory.stock.import-analyze');
        Route::post('/inventory/stock/import-process', [InventoryStockController::class, 'importProcess'])->name('inventory.stock.import-process');
    });

    Route::get('/inventory/stock/{id}/usage-detail', [InventoryStockController::class, 'usageDetail'])->name('inventory.stock.usage-detail');
    Route::get('/inventory/stock/{id}/use', [InventoryStockController::class, 'useItemForm'])->name('inventory.stock.use.item');
    Route::post('/inventory/stock/{id}/use', [InventoryStockController::class, 'processUseItem'])->name('inventory.stock.process-use');

    /* ─────────────────────────────────────────────
       CONSUMABLE REQUEST
    ───────────────────────────────────────────── */
    Route::get('/my-consumable-requests', [ConsumableRequestController::class, 'myRequests'])->name('consumable-requests.my-index');
    Route::get('/consumable-requests/create-form', [ConsumableRequestController::class, 'createForm'])->name('consumable-requests.create-form');
    Route::get('/consumable-requests/create', [ConsumableRequestController::class, 'create'])->name('consumable-requests.create');
    Route::post('/consumable-requests', [ConsumableRequestController::class, 'store'])->name('consumable-requests.store');
    Route::get('/consumable-requests/{consumableRequest}', [ConsumableRequestController::class, 'show'])->name('consumable-requests.show');
    Route::get('/consumable-requests/{consumableRequest}/edit', [ConsumableRequestController::class, 'edit'])->name('consumable-requests.edit');
    Route::put('/consumable-requests/{consumableRequest}', [ConsumableRequestController::class, 'update'])->name('consumable-requests.update');
    Route::get('/consumable-requests/{consumableRequest}/print', [ConsumableRequestController::class, 'print'])->name('consumable-requests.print');
    Route::get('/consumable-requests/{consumableRequest}/pdf', [ConsumableRequestController::class, 'pdf'])->name('consumable-requests.pdf');

    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('/consumable-requests', [ConsumableRequestController::class, 'index'])->name('consumable-requests.index');
        Route::get('consumable-requests/consolidation', [ConsumableRequestController::class, 'consolidationIndex'])->name('consumable-requests.consolidation');
        Route::post('consumable-requests/consolidation/process', [ConsumableRequestController::class, 'processConsolidation'])->name('consumable-requests.consolidation.process');
        Route::patch('consumable-requests/{consumableRequest}/status', [ConsumableRequestController::class, 'updateStatus'])->name('consumable-requests.update-status');
        Route::delete('/consumable-requests/{consumableRequest}', [ConsumableRequestController::class, 'destroy'])->name('consumable-requests.destroy');
        Route::post('/consumable-requests/{consumableRequest}/approve', [ConsumableRequestController::class, 'approve'])->name('consumable-requests.approve');
        Route::post('/consumable-requests/{consumableRequest}/reject', [ConsumableRequestController::class, 'reject'])->name('consumable-requests.reject');
    });

    /* ─────────────────────────────────────────────
       API — Item Lookup & Search
    ───────────────────────────────────────────── */
    Route::get('/api/items/lookup/{barcode}', [ItemLookupController::class, 'byBarcode'])->name('items.lookup');

    Route::get('/api/items/search', function (Request $request) {
        $q = $request->query('q');

        return \App\Models\Item::with('unit')
            ->where(function ($w) use ($q) {
                $w->where('item_name', 'like', "%{$q}%")
                  ->orWhere('item_code', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'id'            => $item->id,
                'name'          => $item->item_name,
                'unit'          => $item->unit->code ?? '-',
                'current_stock' => $item->current_stock,
            ]);
    })->name('items.search');

    /* ─────────────────────────────────────────────
       PROCUREMENT & VENDOR COMPARISON
    ───────────────────────────────────────────── */
    Route::prefix('procurement')->name('procurement.')->group(function () {

        // ═══ LIST & GLOBAL ═══
        Route::middleware('role:Super Admin|Admin')->group(function () {
            Route::get('/', [ProcurementRequestController::class, 'index'])->name('index');
            Route::get('/comparisons-list', [VendorComparisonController::class, 'globalIndex'])->name('comparisons.global');
        });

        // ═══ CREATE & STORE PR AWAL ═══
        Route::get('/create', [ProcurementRequestController::class, 'create'])->name('create');
        Route::post('/', [ProcurementRequestController::class, 'store'])->name('store');

        // ═══════════════════════════════════════════════════════════
        // VENDOR COMPARISON (per PR)
        // ═══════════════════════════════════════════════════════════
        Route::prefix('/{procurement}/comparisons')->name('comparisons.')->group(function () {

            // Index & store vendor
            Route::get('/',  [VendorComparisonController::class, 'index'])->name('index');
            Route::post('/', [VendorComparisonController::class, 'store'])->name('store');

            // AI Scan
            Route::post('/scan',          [VendorComparisonController::class, 'scanImage'])->name('scan');
            Route::post('/scan-monotaro', [VendorComparisonController::class, 'scanMonotaro'])->name('scan.monotaro');
            Route::post('/scan-others',   [VendorComparisonController::class, 'scanOthers'])->name('scan.others');

            // Select winner (manual override)
            Route::patch('/{vendor}/select', [VendorComparisonController::class, 'selectVendor'])->name('select');

            // Delete vendor
            Route::delete('/{vendor}', [VendorComparisonController::class, 'destroy'])->name('destroy');

            // ✅ CREATE FINAL PR — form & simpan
            Route::get('/final-pr/create', [VendorComparisonController::class, 'createFinalPr'])->name('final-pr.create');
            Route::post('/final-pr/store', [VendorComparisonController::class, 'storeFinalPr'])->name('final-pr.store');
        });

        // ═══════════════════════════════════════════════════════════
        // PDF, PRINT, VALIDASI
        // ═══════════════════════════════════════════════════════════
        Route::get('/{procurementRequest}/pdf-final', [ProcurementRequestController::class, 'pdfFinal'])->name('pdf.final');
        Route::get('/{procurementRequest}/pdf',       [ProcurementRequestController::class, 'pdf'])->name('pdf');
        Route::get('/{procurementRequest}/print',     [ProcurementRequestController::class, 'print'])->name('print');

        // ✅ ROUTE VALIDASI TTD PER KOLOM (DIPERBAIKI)
        Route::patch('/{procurementRequest}/signature', [ProcurementRequestController::class, 'updateSignatureStatus'])
            ->name('signature.update');

        Route::patch('/{procurementRequest}/validate-signature', [ProcurementRequestController::class, 'validateSignature'])
            ->name('validate-signature')
            ->middleware('role:Super Admin');

        Route::patch('/{procurementRequest}/validate-final-signature', [ProcurementRequestController::class, 'validateFinalSignature'])
            ->name('validate-final-signature')
            ->middleware('role:Super Admin');

        Route::patch('/{procurementRequest}/status', [ProcurementRequestController::class, 'updateStatus'])
            ->name('status')
            ->middleware('role:Super Admin');

        // ═══════════════════════════════════════════════════════════
        // PURCHASE ORDER (PO) & RECEIVING
        // ═══════════════════════════════════════════════════════════
        Route::get('/purchases',                       [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/{purchase}',            [PurchaseController::class, 'show'])->name('purchases.show');
        Route::post('/purchases/{purchase}/receive',   [PurchaseController::class, 'receive'])->name('purchases.receive');
        Route::get('/{procurement}/purchases/create',  [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/{procurement}/purchases',        [PurchaseController::class, 'store'])->name('purchases.store');

        // ═══════════════════════════════════════════════════════════
        // CRUD PR — PALING BAWAH agar wildcard tidak menangkap route di atas
        // ═══════════════════════════════════════════════════════════
        Route::get('/{procurementRequest}',      [ProcurementRequestController::class, 'show'])->name('show');
        Route::get('/{procurementRequest}/edit', [ProcurementRequestController::class, 'edit'])->name('edit');
        Route::put('/{procurementRequest}',      [ProcurementRequestController::class, 'update'])->name('update');
        Route::delete('/{procurementRequest}',   [ProcurementRequestController::class, 'destroy'])->name('destroy');
    });

    /* ─────────────────────────────────────────────
       REIMBURSEMENT
    ───────────────────────────────────────────── */
    Route::get('reimbursement/{reimbursement}/print', [ReimbursementController::class, 'print'])->name('reimbursement.print');
    Route::get('reimbursement/{reimbursement}/pdf', [ReimbursementController::class, 'pdf'])->name('reimbursement.pdf');
    Route::resource('reimbursement', ReimbursementController::class);
    Route::post('reimbursement/{reimbursement}/approve', [ReimbursementController::class, 'approve'])->name('reimbursement.approve');
});

/* ═══════════════════════════════════════════════════════════════
   RUTE SEMENTARA — HANYA UNTUK LOCAL / STAGING
═══════════════════════════════════════════════════════════════════ */
if (app()->environment('local', 'staging')) {

    Route::get('/jalankan-migrasi-baru', function () {
        try {
            \Illuminate\Support\Facades\Schema::dropIfExists('purchase_items');
            \Illuminate\Support\Facades\Schema::dropIfExists('purchases');

            \Illuminate\Support\Facades\DB::table('migrations')
                ->where('migration', 'like', '%create_purchases%')
                ->orWhere('migration', 'like', '%fix_purchases%')
                ->delete();

            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

            return '<h3>Berhasil!</h3><p>Migrasi baru sudah berjalan.</p><pre>' . \Illuminate\Support\Facades\Artisan::output() . '</pre>';
        } catch (\Exception $e) {
            return '<h3>Error:</h3> ' . $e->getMessage();
        }
    });

    Route::get('/clear-cache', function () {
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        return 'Cache (Route, Config, View) berhasil dibersihkan!';
    });
}