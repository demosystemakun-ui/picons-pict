<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\DepartmentStock;
use App\Models\StockIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    // Status PR 2 yang diizinkan untuk membuat PO
    private const PO_ALLOWED_STATUSES = ['final_approved', 'received', 'completed'];
    
    // Helper untuk cek role yang boleh membuat/manage PO
    private function isAdminOrDeptAdmin(): bool
    {
        return auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Admin Departemen']);
    }

    /* ═══════════════════════════════════════════════════════════════
       INDEX — Daftar Purchase Order (Semua role bisa melihat)
    ═══════════════════════════════════════════════════════════════ */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Purchase::with(['vendor', 'procurementRequest.department'])->latest();

        // PEMBATASAN AKSES: 
        // 1. Admin Departemen -> Hanya PO departemennya
        // 2. User Biasa -> Hanya PO yang terkait dengan PR miliknya
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                $query->whereHas('procurementRequest', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } else {
                $query->whereHas('procurementRequest', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }
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
       CREATE — Form Checkout (HANYA ADMIN/ADMIN DEPT)
    ═══════════════════════════════════════════════════════════════ */
    public function create(ProcurementRequest $procurement)
    {
        // ═══ PENGAMANAN ROLE ═══
        if (!$this->isAdminOrDeptAdmin()) {
            abort(403, 'Only Admin can create a Purchase Order.');
        }

        $user = auth()->user();

        if ($user->hasRole('Admin Departemen') && $procurement->department_id !== $user->department_id) {
            abort(403, 'Anda tidak memiliki akses untuk membuat PO dari PR departemen lain.');
        }

        // ═══ CEK STATUS DARI PR 2 (FINAL COMPARISON) ═══
        $currentStatus = $procurement->finalComparison?->status;

        if (!in_array($currentStatus, self::PO_ALLOWED_STATUSES, true)) {
            return redirect()
                ->route('procurement.show', $procurement)
                ->with('error', 'Purchase Order can only be created from a validated Final PR (Final Approved). Current status: ' . ($currentStatus ?? 'unknown') . '.');
        }

        // Cegah PO ganda
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
       STORE — Proses Simpan PO (HANYA ADMIN/ADMIN DEPT)
    ═══════════════════════════════════════════════════════════════ */
    public function store(Request $request, ProcurementRequest $procurement)
    {
        // ═══ PENGAMANAN ROLE ═══
        if (!$this->isAdminOrDeptAdmin()) {
            abort(403, 'Only Admin can create a Purchase Order.');
        }

        $user = auth()->user();

        if ($user->hasRole('Admin Departemen') && $procurement->department_id !== $user->department_id) {
            abort(403, 'Anda tidak memiliki akses untuk membuat PO dari PR departemen lain.');
        }

        // ═══ CEK STATUS DARI PR 2 (FINAL COMPARISON) ═══
        $currentStatus = $procurement->finalComparison?->status;

        if (!in_array($currentStatus, self::PO_ALLOWED_STATUSES, true)) {
            return back()->with('error', 'Failed to create PO: Invalid PR status (' . ($currentStatus ?? 'unknown') . ').');
        }

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

            $purchase = Purchase::create([
                'procurement_request_id' => $procurement->id,
                'vendor_id'              => $vendor->id,
                'po_number'              => $validated['po_number'],
                'order_date'             => $validated['order_date'],
                'estimated_arrival'      => $validated['estimated_arrival'] ?? null,
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

        // Filter data sesuai role
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                $procurement = $purchase->procurementRequest;
                if ($procurement->department_id !== $user->department_id) {
                    abort(403, 'Anda tidak memiliki akses ke PO departemen lain.');
                }
            } else {
                $procurement = $purchase->procurementRequest;
                if ($procurement->user_id !== $user->id) {
                    abort(403, 'Anda tidak memiliki akses ke PO ini.');
                }
            }
        }

        $purchase->load('items', 'procurementRequest.items.department', 'vendor');
        return view('procurement.purchase.show', compact('purchase'));
    }

    /* ═══════════════════════════════════════════════════════════════
       RECEIVE — Proses Penerimaan Barang (HANYA ADMIN/ADMIN DEPT)
    ═══════════════════════════════════════════════════════════════ */
    public function receive(Request $request, Purchase $purchase)
    {
        // ═══ PENGAMANAN ROLE ═══
        if (!$this->isAdminOrDeptAdmin()) {
            abort(403, 'Only Admin can process goods receipt.');
        }

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
            'items.*.stock_item_id' => 'nullable|exists:items,id',
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

                $item->qty_received += $qtyReceived;
                $item->qty_damaged  += $qtyDamaged;
                $item->save();

                if ($item->qty_received < $item->qty_ordered) {
                    $allReceived = false;
                }

                if (!empty($data['breakdowns'])) {
                    $totalAlokasi = (int) collect($data['breakdowns'])->sum('qty_received');

                    if ($totalAlokasi !== $qtyReceived) {
                        throw new \Exception("Total alokasi departemen ({$totalAlokasi}) tidak sama dengan Qty Datang ({$qtyReceived}) untuk item {$item->product_name}.");
                    }

                    $stockItemId = $data['stock_item_id'] ?? null;
                    if (!$stockItemId) {
                        throw new \Exception("Pilih barang stok untuk item {$item->product_name} sebelum mengalokasikan ke departemen.");
                    }

                    foreach ($data['breakdowns'] as $bd) {
                        $qty = (int) $bd['qty_received'];
                        if ($qty <= 0) continue;

                        $departmentStock = DepartmentStock::firstOrCreate(
                            [
                                'department_id' => $bd['department_id'],
                                'item_id'       => $stockItemId,
                            ],
                            ['stock' => 0]
                        );

                        $departmentStock->stock += $qty;
                        $departmentStock->save();

                        StockIn::create([
                            'department_stock_id' => $departmentStock->id,
                            'user_id'             => auth()->id(),
                            'quantity_in'         => $qty,
                            'note'                => 'Penerimaan PO ' . $purchase->po_number,
                        ]);
                    }
                }
            }

            $purchase->update([
                'status' => $allReceived ? 'completed' : 'partially_received'
            ]);

            $procurementRequest = $purchase->procurementRequest;
            if ($allReceived && $procurementRequest->finalComparison?->status === 'final_approved') {
                $procurementRequest->finalComparison->update(['status' => ProcurementRequest::STATUS_RECEIVED]);
            }

            DB::commit();
            return back()->with('success', 'Data penerimaan barang berhasil disimpan dan didistribusikan ke departemen.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses penerimaan: ' . $e->getMessage())->withInput();
        }
    }
}