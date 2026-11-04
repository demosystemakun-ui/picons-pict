<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Department;
use App\Models\InventoryStock; // Pastikan model ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    // Halaman Form Checkout (Pembuatan PO)
    public function create(ProcurementRequest $procurement)
    {
        if (!in_array($procurement->status, [ProcurementRequest::STATUS_APPROVED, ProcurementRequest::STATUS_PROCESSED])) {
            return redirect()
                ->route('procurement.show', $procurement)
                ->with('error', 'Purchase Order hanya dapat dibuat dari pengajuan yang sudah disetujui/divalidasi.');
        }

        $procurement->load('vendors.items');
        return view('procurement.purchase.create', compact('procurement'));
    }

    // Proses Simpan PO
    public function store(Request $request, ProcurementRequest $procurement)
    {
        if (!in_array($procurement->status, [ProcurementRequest::STATUS_APPROVED, ProcurementRequest::STATUS_PROCESSED])) {
            return back()->with('error', 'Gagal membuat PO: Status pengajuan tidak valid (' . $procurement->status . ').');
        }

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

            $purchase = Purchase::create([
                'procurement_request_id' => $procurement->id,
                'vendor_id'              => $vendor->id,
                'po_number'              => $validated['po_number'],
                'order_date'             => $validated['order_date'],
                'estimated_arrival'      => $validated['estimated_arrival'],
                'status'                 => 'ordered',
                'notes'                  => $validated['notes'] ?? null,
            ]);

            foreach ($vendor->items as $item) {
                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'product_name' => $item->product_name,
                    'qty_ordered'  => $item->quantity,
                    'unit_price'   => $item->unit_price,
                ]);
            }

            if ($procurement->status === ProcurementRequest::STATUS_APPROVED) {
                $procurement->update(['status' => ProcurementRequest::STATUS_PROCESSED]);
            }

            DB::commit();
            
            return redirect()
                ->route('procurement.purchases.show', $purchase->id)
                ->with('success', 'Purchase Order berhasil dibuat. Menunggu barang datang.');
                
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat PO: ' . $e->getMessage());
        }
    }

    // Halaman Detail PO & Form Penerimaan Barang
    public function show(Purchase $purchase)
    {
        $purchase->load('items', 'procurementRequest.items.department', 'vendor');
        return view('procurement.purchase.show', compact('purchase'));
    }

    // Proses Penerimaan Barang
    public function receive(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_items,id',
            'items.*.qty_received' => 'required|integer|min:0',
            'items.*.qty_damaged' => 'required|integer|min:0',
            'items.*.breakdowns' => 'nullable|array',
            'items.*.breakdowns.*.department_id' => 'required_with:items.*.breakdowns|exists:departments,id',
            'items.*.breakdowns.*.qty_received' => 'required_with:items.*.breakdowns|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $allReceived = true;

            foreach ($validated['items'] as $data) {
                $item = PurchaseItem::findOrFail($data['id']);
                $qtyReceived = (int) $data['qty_received'];
                $qtyDamaged = (int) $data['qty_damaged'];

                // Update item PO
                $item->qty_received += $qtyReceived;
                $item->qty_damaged += $qtyDamaged;
                $item->save();

                if ($item->qty_received < $item->qty_ordered) {
                    $allReceived = false;
                }

                // Proses Distribusi ke Departemen
                if (!empty($data['breakdowns'])) {
                    $totalAlokasi = collect($data['breakdowns'])->sum('qty_received');

                    // Validasi: Total alokasi harus sama dengan total barang datang (fisik)
                    if ($totalAlokasi !== $qtyReceived) {
                        throw new \Exception("Total alokasi per departemen ({$totalAlokasi}) tidak sama dengan Qty Datang Fisik ({$qtyReceived}) untuk item {$item->product_name}.");
                    }

                    foreach ($data['breakdowns'] as $bd) {
                        if ($bd['qty_received'] > 0) {
                            // Simpan ke Inventory / Stok Departemen
                            // Asumsi: Ada model InventoryStock dengan kolom department_id, item_name, quantity, type, notes
                            InventoryStock::create([
                                'department_id' => $bd['department_id'],
                                'item_name'     => $item->product_name,
                                'quantity'      => $bd['qty_received'],
                                'type'          => 'in',
                                'notes'         => 'Penerimaan PO ' . $purchase->po_number,
                            ]);
                        }
                    }
                }
            }

            // Update Status PO
            $purchase->update([
                'status' => $allReceived ? 'completed' : 'partially_received'
            ]);

            // Update Status PR
            $procurementRequest = $purchase->procurementRequest;
            if ($allReceived && $procurementRequest->status === ProcurementRequest::STATUS_PROCESSED) {
                $procurementRequest->update(['status' => ProcurementRequest::STATUS_RECEIVED]);
            }

            DB::commit();
            return back()->with('success', 'Data penerimaan barang berhasil disimpan dan didistribusikan ke departemen.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses penerimaan: ' . $e->getMessage())->withInput();
        }
    }
}