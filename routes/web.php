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

    Route::resource('categories', CategoryController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('units', UnitController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('vendors', VendorController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('departments', DepartmentController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::middleware(['role:Super Admin'])->group(function () {
        Route::resource('signers', SignerController::class)
            ->except(['show', 'create']);

             Route::resource('users', UserController::class)->except(['show']);
    });

    /* ─────────────────────────────────────────────
       INVENTORY STOCK
    ───────────────────────────────────────────── */
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('/inventory/stock/create', [InventoryStockController::class, 'create'])->name('inventory.stock.create');
        Route::post('/inventory/stock',       [InventoryStockController::class, 'store'])->name('inventory.stock.store');
    });

    // ⚠️ STATIC ROUTES — HARUS DI ATAS route dengan {id} agar tidak konflik
    Route::get('/inventory/stock/export-usage', [InventoryStockController::class, 'exportUsage'])->name('inventory.stock.export-usage');
    Route::get('/inventory/stock/use',          [InventoryStockController::class, 'useStockForm'])->name('inventory.stock.use');

    // Dynamic routes (dengan {id})
    Route::get('/inventory/stock/{id}/use',   [InventoryStockController::class, 'useItemForm'])->name('inventory.stock.use.item');
    Route::post('/inventory/stock/{id}/use',  [InventoryStockController::class, 'processUseItem'])->name('inventory.stock.process-use');

    // Index (paling bawah)
    Route::get('/inventory/stock', [InventoryStockController::class, 'index'])->name('inventory.stock.index');

    /* ─────────────────────────────────────────────
       CONSUMABLE REQUEST
    ───────────────────────────────────────────── */
    Route::get('/my-consumable-requests', [ConsumableRequestController::class, 'myRequests'])
        ->name('consumable-requests.my-index');

    // Form partial (AJAX) — HARUS di atas {consumableRequest}
    Route::get('/consumable-requests/create-form', [ConsumableRequestController::class, 'createForm'])
        ->name('consumable-requests.create-form');

    Route::get('/consumable-requests/create',  [ConsumableRequestController::class, 'create'])->name('consumable-requests.create');
    Route::post('/consumable-requests',        [ConsumableRequestController::class, 'store'])->name('consumable-requests.store');

    Route::get('/consumable-requests/{consumableRequest}',         [ConsumableRequestController::class, 'show'])->name('consumable-requests.show');
    Route::get('/consumable-requests/{consumableRequest}/edit',    [ConsumableRequestController::class, 'edit'])->name('consumable-requests.edit');
    Route::put('/consumable-requests/{consumableRequest}',         [ConsumableRequestController::class, 'update'])->name('consumable-requests.update');
    Route::get('/consumable-requests/{consumableRequest}/print',   [ConsumableRequestController::class, 'print'])->name('consumable-requests.print');
    Route::get('/consumable-requests/{consumableRequest}/pdf',     [ConsumableRequestController::class, 'pdf'])->name('consumable-requests.pdf');

    // Super Admin only
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('/consumable-requests', [ConsumableRequestController::class, 'index'])->name('consumable-requests.index');

        // Static routes HARUS di atas {consumableRequest}
        Route::get('consumable-requests/consolidation',          [ConsumableRequestController::class, 'consolidationIndex'])->name('consumable-requests.consolidation');
        Route::post('consumable-requests/consolidation/process', [ConsumableRequestController::class, 'processConsolidation'])->name('consumable-requests.consolidation.process');

        Route::patch('consumable-requests/{consumableRequest}/status', [ConsumableRequestController::class, 'updateStatus'])->name('consumable-requests.update-status');
        Route::delete('/consumable-requests/{consumableRequest}',      [ConsumableRequestController::class, 'destroy'])->name('consumable-requests.destroy');
        Route::post('/consumable-requests/{consumableRequest}/approve',[ConsumableRequestController::class, 'approve'])->name('consumable-requests.approve');
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

    /* ═══════════════════════════════════════════════════════════════
       PROCUREMENT & VENDOR COMPARISON
    ═══════════════════════════════════════════════════════════════ */
    Route::prefix('procurement')->name('procurement.')->group(function () {

        /* ─── Super Admin Only: List & Global Comparison ─── */
        Route::middleware('role:Super Admin')->group(function () {
            Route::get('/',                 [ProcurementRequestController::class, 'index'])->name('index');
            Route::get('/comparisons-list', [VendorComparisonController::class, 'globalIndex'])->name('comparisons.global');
        });

        /* ─── Create & Store ─── */
        Route::get('/create', [ProcurementRequestController::class, 'create'])->name('create');
        Route::post('/',      [ProcurementRequestController::class, 'store'])->name('store');

        /* ─── Vendor Comparison ─── */
        Route::get('/{procurement}/comparisons',      [VendorComparisonController::class, 'index'])->name('comparisons.index');
        Route::post('/{procurement}/comparisons',     [VendorComparisonController::class, 'store'])->name('comparisons.store');

        // 3 endpoint scan
        Route::post('/{procurement}/comparisons/scan',           [VendorComparisonController::class, 'scanImage'])->name('comparisons.scan');
        Route::post('/{procurement}/comparisons/scan-monotaro',  [VendorComparisonController::class, 'scanMonotaro'])->name('comparisons.scan.monotaro');
        Route::post('/{procurement}/comparisons/scan-others',    [VendorComparisonController::class, 'scanOthers'])->name('comparisons.scan.others');

        // Select & destroy
        Route::patch('/{procurement}/comparisons/{comparison}/select', [VendorComparisonController::class, 'selectVendor'])->name('comparisons.select');
        Route::delete('/comparisons/{comparison}',                     [VendorComparisonController::class, 'destroy'])->name('comparisons.destroy');

        /* ─── PDF Final ─── */
        Route::get('/{procurementRequest}/pdf-final', [ProcurementRequestController::class, 'pdfFinal'])->name('pdf.final');

        /* ─── Detail PR (dynamic — taruh paling bawah) ─── */
        Route::get('/{procurementRequest}',        [ProcurementRequestController::class, 'show'])->name('show');
        Route::get('/{procurementRequest}/edit',   [ProcurementRequestController::class, 'edit'])->name('edit');
        Route::put('/{procurementRequest}',        [ProcurementRequestController::class, 'update'])->name('update');
        Route::delete('/{procurementRequest}',     [ProcurementRequestController::class, 'destroy'])->name('destroy');
        Route::get('/{procurementRequest}/print',  [ProcurementRequestController::class, 'print'])->name('print');
        Route::get('/{procurementRequest}/pdf',    [ProcurementRequestController::class, 'pdf'])->name('pdf');

        Route::patch('/{procurementRequest}/status',
            [ProcurementRequestController::class, 'updateStatus']
        )->name('status')->middleware('role:Super Admin');
    });

    /* ─────────────────────────────────────────────
       REIMBURSEMENT
    ───────────────────────────────────────────── */
    Route::get('reimbursement/{reimbursement}/print', [ReimbursementController::class, 'print'])->name('reimbursement.print');
    Route::get('reimbursement/{reimbursement}/pdf',   [ReimbursementController::class, 'pdf'])->name('reimbursement.pdf');
    Route::resource('reimbursement', ReimbursementController::class);
});

    Route::get('/debug-log', function () {
        return [
            'count'    => \App\Models\StockLog::count(),
            'last'     => \App\Models\StockLog::latest()->first(),
            'fillable' => (new \App\Models\StockLog)->getFillable(),
            'table'    => (new \App\Models\StockLog)->getTable(),
            'now'      => now()->toDateTimeString(),
        ];
    });