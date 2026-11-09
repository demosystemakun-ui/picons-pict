<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\InventoryStock; // Pastikan Model ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Status PR yang boleh dibuatkan PO.
     * PO hanya boleh dibuat setelah PR Final divalidasi (TTD Final).
     */
    private const PO_ALLOWED_STATUSES = ['final_approved'];

    /* ═══════════════════════════════════════════════════════════════
       INDEX — Daftar Purchase Order
    ═══════════════════════════════════════════════════════════════ */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Purchase::with(['vendor', 'procurementRequest.department'])->latest();

        // PEMBATASAN AKSES: Admin Departemen hanya lihat PO departemennya
        if ($user->hasRole('Admin Departemen')) {
            $query->whereHas('procurementRequest', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        // Fitur Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Fitur Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn ($v) => $v->where('vendor_name', 'like', "%{$search}%"));
            });
        }

        $purchases = $query->paginate(15)->appends($request->all());

        return view('procurement.purchase.index', compact('purchases'));
    }

    /* ═══════════════════════════════════════════════════════════════
       CREATE — Form Checkout (Pembuatan PO)
    ═══════════════════════════════════════════════════════════════ */
    public function create(ProcurementRequest $procurement)
    {
        $user = auth()->user();

        if ($user->hasRole('Admin Departemen') && $procurement->department_id !== $user->department_id) {
            abort(403, 'Anda tidak memiliki akses untuk membuat PO dari PR departemen lain.');
        }

        if (!in_array($procurement->status, self::PO_ALLOWED_STATUSES, true)) {
            return redirect()
                ->route('procurement.show', $procurement)
                ->with('error', 'Purchase Order hanya dapat dibuat dari PR Final yang sudah divalidasi (Final Approved). Status saat ini: ' . $procurement->status . '.');
        }

        // Cegah PO ganda untuk PR yang sama
        $existingPo = Purchase::where('procurement_request_id', $procurement->id)->first();
        if ($existingPo) {
            return redirect()
                ->route('procurement.purchases.show', $existingPo->id)
                ->with('error', 'PO untuk PR ini sudah dibuat (' . $existingPo->po_number . ').');
        }

        $procurement->load('vendors.items');
        return view('procurement.purchase.create', compact('procurement'));
    }

    /* ═══════════════════════════════════════════════════════════════
       STORE — Proses Simpan PO
    ═══════════════════════════════════════════════════════════════ */
    public function store(Request $request, ProcurementRequest $procurement)
    {
        $user = auth()->user();

        if ($user->hasRole('Admin Departemen') && $procurement->department_id !== $user->department_id) {
            abort(403, 'Anda tidak memiliki akses untuk membuat PO dari PR departemen lain.');
        }

        if (!in_array($procurement->status, self::PO_ALLOWED_STATUSES, true)) {
            return back()->with('error', 'Gagal membuat PO: Status pengajuan tidak valid (' . $procurement->status . ').');
        }

        // Cegah PO ganda untuk PR yang sama
        $existingPo = Purchase::where('procurement_request_id', $procurement->id)->first();
        if ($existingPo) {
            return redirect()
                ->route('procurement.purchases.show', $existingPo->id)
                ->with('error', 'PO untuk PR ini sudah dibuat (' . $existingPo->po_number . ').');
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

            // Buat Purchase Record
            $purchase = Purchase::create([
                'procurement_request_id' => $procurement->id,
                'vendor_id'              => $vendor->id,
                'po_number'              => $validated['po_number'],
                'order_date'             => $validated['order_date'],
                'estimated_arrival'      => $validated['estimated_arrival'] ?? null,
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

            // Status PR tetap 'final_approved' sampai barang diterima (receive()).
            // Tidak ada perubahan status PR di sini.

            DB::commit();

            return redirect()
                ->route('procurement.purchases.show', $purchase->id)
                ->with('success', 'Purchase Order berhasil dibuat. Menunggu barang datang.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat PO: ' . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       SHOW — Detail PO & Form Penerimaan Barang
    ═══════════════════════════════════════════════════════════════ */
    public function show(Purchase $purchase)
    {
        $user = auth()->user();

        if ($user->hasRole('Admin Departemen')) {
            $procurement = $purchase->procurementRequest;
            if ($procurement->department_id !== $user->department_id) {
                abort(403, 'Anda tidak memiliki akses ke PO departemen lain.');
            }
        }

        $purchase->load('items', 'procurementRequest.items.department', 'vendor');
        return view('procurement.purchase.show', compact('purchase'));
    }

    /* ═══════════════════════════════════════════════════════════════
       RECEIVE — Proses Penerimaan Barang & Distribusi Stok
    ═══════════════════════════════════════════════════════════════ */
    public function receive(Request $request, Purchase $purchase)
    {
        $user = auth()->user();

        if ($user->hasRole('Admin Departemen')) {
            $procurement = $purchase->procurementRequest;
            if ($procurement->department_id !== $user->department_id) {
                abort(403, 'Anda tidak memiliki akses ke PO departemen lain.');
            }
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_items,id',
            'items.*.qty_received' => 'required|integer|min:0',
            'items.*.qty_damaged' => 'required|integer|min:0|lte:items.*.qty_received',
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
                $qtyDamaged  = (int) $data['qty_damaged'];

                // Update qty penerimaan
                $item->qty_received += $qtyReceived;
                $item->qty_damaged  += $qtyDamaged;
                $item->save();

                if ($item->qty_received < $item->qty_ordered) {
                    $allReceived = false;
                }

                // DISTRIBUSI STOK KE DEPARTEMEN
                if (!empty($data['breakdowns'])) {
                    $totalAlokasi = (int) collect($data['breakdowns'])->sum('qty_received');

                    if ($totalAlokasi !== $qtyReceived) {
                        throw new \Exception("Total alokasi departemen ({$totalAlokasi}) tidak sama dengan Qty Datang ({$qtyReceived}) untuk item {$item->product_name}.");
                    }

                    foreach ($data['breakdowns'] as $bd) {
                        if ($bd['qty_received'] > 0) {
                            // Catat ke inventaris departemen
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

            // Update Status PR: final_approved -> received (alur baru)
            $procurementRequest = $purchase->procurementRequest;
            if ($allReceived && $procurementRequest->status === 'final_approved') {
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