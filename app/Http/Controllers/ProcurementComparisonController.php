<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\ProcurementComparison;
use Illuminate\Http\Request;

class ProcurementComparisonController extends Controller
{
    /**
     * Form input comparison vendor untuk PR tertentu.
     */
    public function create(ProcurementRequest $procurementRequest)
    {
        $comparisons = $procurementRequest->comparisons()->get();
        return view('procurement.comparison.create', compact('procurementRequest', 'comparisons'));
    }

    /**
     * Simpan comparison baru (bisa banyak sekaligus dari form).
     */
    public function store(Request $request, ProcurementRequest $procurementRequest)
    {
        $validated = $request->validate([
            'vendors'                => 'required|array|min:1',
            'vendors.*.vendor_name'  => 'required|string|max:255',
            'vendors.*.company_name' => 'nullable|string|max:255',
            'vendors.*.total_price'  => 'required|numeric|min:0',
            'vendors.*.tax_note'     => 'nullable|string|max:50',
            'vendors.*.reason'       => 'nullable|string',
        ]);

        // Hapus yang lama (kalau admin re-input)
        $procurementRequest->comparisons()->delete();

        foreach ($validated['vendors'] as $index => $vendor) {
            $procurementRequest->comparisons()->create([
                'vendor_name'  => $vendor['vendor_name'],
                'company_name' => $vendor['company_name'] ?? null,
                'total_price'  => $vendor['total_price'],
                'tax_note'     => $vendor['tax_note'] ?? null,
                'reason'       => $vendor['reason'] ?? null,
                'sort_order'   => $index + 1,
            ]);
        }

        return redirect()
            ->route('procurement.comparisons.create', $procurementRequest)
            ->with('success', 'Comparison vendor berhasil disimpan.');
    }

    /**
     * Pilih 1 vendor sebagai pemenang + input reason.
     */
    public function select(Request $request, ProcurementRequest $procurementRequest)
    {
        $validated = $request->validate([
            'comparison_id'         => 'required|exists:procurement_comparisons,id',
            'reason_choose_vendor'  => 'required|string|max:2000',
            'budget_type'           => 'nullable|in:budgeted,non_budgeted',
            'accounting_mgr_name'   => 'nullable|string|max:255',
            'ceo_name'              => 'nullable|string|max:255',
            'cfo_name'              => 'nullable|string|max:255',
        ]);

        // Reset semua comparison → set 1 sebagai selected
        $procurementRequest->comparisons()->update(['is_selected' => false]);

        $selected = $procurementRequest->comparisons()->findOrFail($validated['comparison_id']);
        $selected->update(['is_selected' => true]);

        // Update PR
        $procurementRequest->update([
            'final_vendor_id'       => $selected->id,
            'final_total_price'     => $selected->total_price,
            'reason_choose_vendor'  => $validated['reason_choose_vendor'],
            'budget_type'           => $validated['budget_type'] ?? $procurementRequest->budget_type,
            'accounting_mgr_name'   => $validated['accounting_mgr_name'] ?? $procurementRequest->accounting_mgr_name,
            'ceo_name'              => $validated['ceo_name'] ?? $procurementRequest->ceo_name,
            'cfo_name'              => $validated['cfo_name'] ?? $procurementRequest->cfo_name,
        ]);

        return redirect()
            ->route('procurement.show', $procurementRequest)
            ->with('success', 'Vendor berhasil dipilih sebagai pemenang.');
    }
}