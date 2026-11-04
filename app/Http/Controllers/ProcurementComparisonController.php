<?php
// ProcurementComparisonController.php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\ProcurementVendor;
use Illuminate\Http\Request;

class ProcurementComparisonController extends Controller
{
    public function create(ProcurementRequest $procurementRequest)
    {
        // Ambil dari sistem baru
        $vendors = $procurementRequest->vendors()->get();
        return view('procurement.comparison.create', compact('procurementRequest', 'vendors'));
    }

    public function store(Request $request, ProcurementRequest $procurementRequest)
    {
        $validated = $request->validate([
            'vendors'                => 'required|array|min:1',
            'vendors.*.vendor_name'  => 'required|string|max:255',
            'vendors.*.company_name' => 'nullable|string|max:255',
            'vendors.*.total_price'  => 'required|numeric|min:0',
            'vendors.*.tax_note'     => 'nullable|string|max:50',
        ]);

        // Hapus vendor lama jika admin re-input
        $procurementRequest->vendors()->delete();

        foreach ($validated['vendors'] as $index => $vendor) {
            $procurementRequest->vendors()->create([
                'vendor_name'  => $vendor['vendor_name'],
                'company_name' => $vendor['company_name'] ?? null,
                'total_price'  => $vendor['total_price'],
                'tax_note'     => $vendor['tax_note'] ?? null,
                'sort_order'   => $index + 1,
                'is_winner'    => false, // default
            ]);
        }

        return redirect()
            ->route('procurement.show', $procurementRequest)
            ->with('success', 'Perbandingan vendor berhasil disimpan. Silakan pilih pemenang.');
    }

    public function select(Request $request, ProcurementRequest $procurementRequest)
    {
        $validated = $request->validate([
            'vendor_id'             => 'required|exists:procurement_vendors,id',
            'reason_choose_vendor'  => 'required|string|max:2000',
            'budget_type'           => 'nullable|in:budgeted,non_budgeted',
            'accounting_mgr_name'   => 'nullable|string|max:255',
            'ceo_name'              => 'nullable|string|max:255',
            'cfo_name'              => 'nullable|string|max:255',
        ]);

        // Reset semua vendor → bukan pemenang
        $procurementRequest->vendors()->update(['is_winner' => false]);

        // Set 1 vendor sebagai pemenang
        $winner = $procurementRequest->vendors()->findOrFail($validated['vendor_id']);
        $winner->update(['is_winner' => true]);

        // Update PR
        $procurementRequest->update([
            'final_vendor_id'      => null, // Kosongkan legacy FK
            'final_total_price'    => $winner->total_price,
            'reason_choose_vendor' => $validated['reason_choose_vendor'],
            'budget_type'          => $validated['budget_type'] ?? $procurementRequest->budget_type,
            'accounting_mgr_name'  => $validated['accounting_mgr_name'] ?? $procurementRequest->accounting_mgr_name,
            'ceo_name'             => $validated['ceo_name'] ?? $procurementRequest->ceo_name,
            'cfo_name'             => $validated['cfo_name'] ?? $procurementRequest->cfo_name,
        ]);

        return redirect()
            ->route('procurement.show', $procurementRequest)
            ->with('success', 'Vendor pemenang berhasil dipilih.');
    }
}