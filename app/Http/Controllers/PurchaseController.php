<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    // Halaman Form Checkout (Pembuatan PO)
    public function create(ProcurementRequest $procurement)
    {
        $procurement->load('vendors.items');
        return view('procurement.purchase.create', compact('procurement'));
    }

    // Proses Simpan PO
    public function store(Request $request, ProcurementRequest $procurement)
    {
        $validated = $request->validate([
            'vendor_id'         => 'required|exists:procurement_vendors,id',
            'po_number'         => 'required|string|unique:purchases,po_number',
            'order_date'        => 'required|date',
            'estimated_arrival' => 'nullable|date|after_or_equal:order_date',
            'notes'             => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $vendor = $procurement->vendors()->findOrFail($validated['vendor_id']);

            // Buat Purchase Record
            $purchase = Purchase::create([
                'procurement_request_id' => $procurement->id,
                'vendor_id'              => $vendor->id,
                'po_number'              => $validated['po_number'],
                'order_date'             => $validated['order_date'],
                'estimated_arrival'      => $validated['estimated_arrival'],
                'status'                 => 'ordered',
                'notes'                  => $validated['notes'] ?? null,
            ]);

            // Copy item dari Vendor Comparison ke Purchase Items
            foreach ($vendor->items as $item) {
                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'product_name' => $item->product_name,
                    'qty_ordered'  => $item->quantity,
                    'unit_price'   => $item->unit_price,
                ]);
            }

            if ($procurement->status !== ProcurementRequest::STATUS_APPROVED) {
                throw new \RuntimeException('Purchase Order hanya dapat dibuat dari pengajuan yang sudah disetujui.');
            }
            $procurement->update(['status' => ProcurementRequest::STATUS_PROCESSED]);

            DB::commit();
            return redirect()->route('procurement.purchases.show', $purchase->id)
                ->with('success', 'Purchase Order berhasil dibuat. Menunggu barang datang.');
                
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat PO: ' . $e->getMessage());
        }
    }

    // Halaman Detail PO & Form Penerimaan Barang
    public function show(Purchase $purchase)
    {
        $purchase->load('items', 'procurementRequest', 'vendor');
        return view('procurement.purchase.show', compact('purchase'));
    }

    // Proses Penerimaan Barang
    public function receive(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_items,id',
            'items.*.qty_received' => 'required|integer|min:0',
            'items.*.qty_damaged' => 'required|integer|min:0|lte:items.*.qty_received', // Rusak tidak boleh > diterima
        ]);

        DB::beginTransaction();
        try {
            $allReceived = true;

            foreach ($validated['items'] as $data) {
                $item = PurchaseItem::findOrFail($data['id']);
                
                // Update qty
                $item->qty_received += $data['qty_received'];
                $item->qty_damaged += $data['qty_damaged'];
                $item->save();

                if ($item->qty_received < $item->qty_ordered) {
                    $allReceived = false;
                }
            }

            // Update Status PO
            $purchase->update([
                'status' => $allReceived ? 'completed' : 'partially_received'
            ]);

            $procurementRequest = $purchase->procurementRequest;
            if ($allReceived && $procurementRequest->status === ProcurementRequest::STATUS_PROCESSED) {
                $procurementRequest->update(['status' => ProcurementRequest::STATUS_RECEIVED]);
            }

            DB::commit();
            return back()->with('success', 'Data penerimaan barang berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses penerimaan: ' . $e->getMessage());
        }
    }
}