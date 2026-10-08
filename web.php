<?php

use App\Http\Controllers\ProcurementRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('procurement')->name('procurement.')->group(function () {
    Route::get('/',                 [ProcurementRequestController::class, 'index'])->name('index');
    Route::get('/create',           [ProcurementRequestController::class, 'create'])->name('create');
    Route::post('/',                [ProcurementRequestController::class, 'store'])->name('store');
    Route::get('/{procurementRequest}',         [ProcurementRequestController::class, 'show'])->name('show');
    Route::get('/{procurementRequest}/edit',    [ProcurementRequestController::class, 'edit'])->name('edit');
    Route::put('/{procurementRequest}',         [ProcurementRequestController::class, 'update'])->name('update');
    Route::delete('/{procurementRequest}',      [ProcurementRequestController::class, 'destroy'])->name('destroy');

    // Print / PDF matching the original form layout
    Route::get('/{procurementRequest}/print',   [ProcurementRequestController::class, 'print'])->name('print');
    Route::get('/{procurementRequest}/pdf',     [ProcurementRequestController::class, 'pdf'])->name('pdf');
});
