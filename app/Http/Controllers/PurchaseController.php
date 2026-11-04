<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{

public function index(Request $request)
{
    // Ambil semua PO, urutkan dari yang terbaru
    $query = Purchase::with(['vendor', 'procurementRequest.department'])->latest();

    // Fitur Filter Status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Fitur Pencarian
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('po_number', 'like', "%{$search}%")
              ->orWhereHas('vendor', fn($v) => $v->where('vendor_name', 'like', "%{$search}%"));
        });
    }

    $purchases = $query->paginate(15)->appends($request->all());

    // Pastikan file view ini ada: resources/views/procurement/purchase/index.blade.php
    return view('procurement.purchase.index', compact('purchases'));
}
    // Halaman Form Checkout (Pembuatan PO)
    public function create(ProcurementRequest $procurement)
    {
        // Validasi: Pastikan PR sudah siap untuk PO (approved atau processed)
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
        // ═══ 1. VALIDASI STATUS (DILAKUKAN DI AWAL) ═══
        // Izinkan jika status masih 'approved' (belum divalidasi TTD) 
        // ATAU 'processed' (sudah divalidasi TTD manual)
        if (!in_array($procurement->status, [ProcurementRequest::STATUS_APPROVED, ProcurementRequest::STATUS_PROCESSED])) {
            return back()->with('error', 'Gagal membuat PO: Status pengajuan tidak valid (' . $procurement->status . ').');
        }

        // ═══ 2. VALIDASI INPUT FORM ═══
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

            // ═══ 3. BUAT PURCHASE RECORD ═══
            $purchase = Purchase::create([
                'procurement_request_id' => $procurement->id,
                'vendor_id'              => $vendor->id,
                'po_number'              => $validated['po_number'],
                'order_date'             => $validated['order_date'],
                'estimated_arrival'      => $validated['estimated_arrival'],
                'status'                 => 'ordered',
                'notes'                  => $validated['notes'] ?? null,
            ]);

            // ═══ 4. COPY ITEM DARI VENDOR COMPARISON KE PURCHASE ITEMS ═══
            foreach ($vendor->items as $item) {
                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'product_name' => $item->product_name,
                    'qty_ordered'  => $item->quantity,
                    'unit_price'   => $item->unit_price,
                ]);
            }

            // ═══ 5. UPDATE STATUS PR MENJADI PROCESSED ═══
            // (Jika sebelumnya masih 'approved', ubah ke 'processed'. Jika sudah 'processed', biarkan)
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
            'items.*.qty_damaged' => 'required|integer|min:0|lte:items.*.qty_received',
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