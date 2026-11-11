<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ProcurementRequest;
use App\Models\ConsumableRequest;
use App\Models\Vendor;
use App\Models\DepartmentStock;
use App\Models\CashAdvance;
use App\Models\Liquidation;
use App\Models\Reimbursement;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = auth()->user();
        // Cek apakah user adalah Super Admin / Admin / Admin Departemen
        $isAdmin = $user->hasAnyRole(['Super Admin', 'Admin', 'Admin Departemen']);

        /* ═══════════════════════════════════════════════════════
           JIKA USER BIASA (STAFF) → TAMPILKAN DASHBOARD TRACKING
        ═══════════════════════════════════════════════════════ */
        if (!$isAdmin) {
            // 1. Statistik Pengajuan PR User ini
            $myPRs = ProcurementRequest::where('user_id', $user->id);
            
            $my_pr_total    = (clone $myPRs)->count();
            $my_pr_pending  = (clone $myPRs)->where('status', 'pending')->count();
            $my_pr_approved = (clone $myPRs)->whereIn('status', ['approved', 'processed', 'pending_review_final', 'pending_final_sign', 'final_approved', 'received', 'completed'])->count();
            $my_pr_rejected = (clone $myPRs)->whereIn('status', ['rejected', 'revision'])->count();

            // 2. Riwayat Pengajuan Terakhir
            $recent_prs = (clone $myPRs)->latest()->take(5)->get();

            // 3. Barang Paling Sering Dipakai (Berdasarkan pengajuan barang)
            // Mengambil dari item PR yang paling sering diajukan oleh user ini
            $most_used_items = \App\Models\ProcurementRequestItem::whereHas('procurementRequest', function($q) use ($user) {
                                    $q->where('user_id', $user->id);
                                })
                                ->select('item_name', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_used'))
                                ->groupBy('item_name')
                                ->orderByDesc('total_used')
                                ->take(5)
                                ->get();

            return view('dashboard.user', compact(
                'my_pr_total', 'my_pr_pending', 'my_pr_approved', 'my_pr_rejected', 
                'recent_prs', 'most_used_items'
            ));
        }

        /* ═══════════════════════════════════════════════════════
           JIKA ADMIN → TAMPILKAN DASHBOARD GLOBAL (KODE ANDA)
        ═══════════════════════════════════════════════════════ */
        $procurementQuery = ProcurementRequest::query();
        $consumableQuery  = ConsumableRequest::query();

        // Filter tambahan untuk Admin Departemen
        if ($user->hasRole('Admin Departemen')) {
            $procurementQuery->where('department_id', $user->department_id);
        }

        /* ── STOCK & ITEM METRICS ── */
        $totalItem  = Item::count();
        $totalStock = DepartmentStock::sum('stock') ?? 0;

        $lowStockDeptStocks = DepartmentStock::with(['item.category', 'item.unit', 'department'])
            ->where('stock', '<=', 5)
            ->get();

        $lowStockItems = $lowStockDeptStocks->map(function ($deptStock) {
            $item = $deptStock->item;
            if ($item) {
                $item->current_stock = $deptStock->stock;
                $item->item_name = $item->item_name . ' (' . ($deptStock->department->name ?? '') . ')';
            }
            return $item;
        })->filter();

        $lowStockCount = $lowStockDeptStocks->count();
        $totalVendor   = Vendor::where('is_active', true)->count();

        /* ── PENDING REQUEST ── */
        $pendingProcurement  = (clone $procurementQuery)->where('status', 'pending')->count();
        $pendingConsumable   = (clone $consumableQuery)->where('status', 'pending')->count();
        
        $pendingCashAdvance  = \App\Models\CashAdvance::where('status', 'pending')->count();
        $pendingLiquidation  = \App\Models\Liquidation::where('status', 'pending')->count();
        $pendingReimbursement = \App\Models\Reimbursement::where('status', 'pending')->count();

        $pendingReq = $pendingProcurement + $pendingConsumable + $pendingCashAdvance + $pendingLiquidation + $pendingReimbursement;
        $pendingApp = (clone $procurementQuery)->where('status', 'approved')->count()
                    + (clone $consumableQuery)->where('status', 'approved')->count();

        /* ── STATS UTAMA ── */
        $stats = [
            'total_item'       => $totalItem,
            'total_stock'      => $totalStock,
            'low_stock'        => $lowStockCount,
            'total_vendor'     => $totalVendor,
            'pending_request'  => $pendingReq,
            'pending_approval' => $pendingApp,
            'purchase_order'   => \App\Models\Purchase::count(), // Pastikan model Purchase ada
            'goods_received'   => 0,
        ];

        /* ── BREAKDOWN PER MODUL ── */
        $moduleTotals = [
            // PROCUREMENT
            'procurement_total'    => (clone $procurementQuery)->count(),
            'procurement_pending'  => $pendingProcurement,
            'procurement_approved' => (clone $procurementQuery)->where('status', 'approved')->count(),
            'procurement_rejected' => (clone $procurementQuery)->where('status', 'rejected')->count(),

            // CONSUMABLE
            'consumable_total'     => (clone $consumableQuery)->count(),
            'consumable_pending'   => $pendingConsumable,
            'consumable_approved'  => (clone $consumableQuery)->where('status', 'approved')->count(),
            'consumable_rejected'  => (clone $consumableQuery)->where('status', 'rejected')->count(),

            // CASH ADVANCE
            'cash_advance_total'     => \App\Models\CashAdvance::count(),
            'cash_advance_pending'   => $pendingCashAdvance,
            'cash_advance_approved'  => \App\Models\CashAdvance::where('status', 'approved')->count(),
            'cash_advance_rejected'  => \App\Models\CashAdvance::where('status', 'rejected')->count(),

            // LIQUIDATION
            'liquidation_total'      => \App\Models\Liquidation::count(),
            'liquidation_pending'    => $pendingLiquidation,
            'liquidation_approved'   => \App\Models\Liquidation::where('status', 'approved')->count(),
            'liquidation_rejected'   => \App\Models\Liquidation::where('status', 'rejected')->count(),

            // REIMBURSEMENT
            'reimbursement_total'     => \App\Models\Reimbursement::count(),
            'reimbursement_pending'   => $pendingReimbursement,
            'reimbursement_approved'  => \App\Models\Reimbursement::where('status', 'approved')->count(),
            'reimbursement_rejected'  => \App\Models\Reimbursement::where('status', 'rejected')->count(),
        ];

        return view('dashboard.index', compact('stats', 'moduleTotals', 'lowStockItems'));
    }
}