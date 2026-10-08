<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ProcurementRequest;
use App\Models\ConsumableRequest;
use App\Models\Vendor;
use App\Models\DepartmentStock;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = auth()->user();
        $isAdmin = $user->hasRole('Super Admin');

        /* ═══════════════════════════════════════════════════════
           BASE QUERIES (dengan filter role)
        ═══════════════════════════════════════════════════════ */
        $procurementQuery = ProcurementRequest::query();
        $consumableQuery  = ConsumableRequest::query();

        if (!$isAdmin) {
            $procurementQuery->where('department_id', $user->department_id);
            $consumableQuery->where('user_id', $user->id);
        }

        /* ═══════════════════════════════════════════════════════
           STOCK & ITEM METRICS
        ═══════════════════════════════════════════════════════ */
        if ($isAdmin) {
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
        } else {
            $deptId = $user->department_id;

            $totalStock = DepartmentStock::where('department_id', $deptId)->sum('stock') ?? 0;
            $totalItem  = DepartmentStock::where('department_id', $deptId)
                            ->distinct('item_id')->count('item_id');

            $departmentStocks = DepartmentStock::with(['item.category', 'item.unit'])
                ->where('department_id', $deptId)
                ->where('stock', '<=', 5)
                ->get();

            $lowStockItems = $departmentStocks->map(function ($deptStock) {
                $item = $deptStock->item;
                if ($item) {
                    $item->current_stock = $deptStock->stock;
                }
                return $item;
            })->filter();

            $lowStockCount = $departmentStocks->count();
            $totalVendor   = 0;
        }

        /* ═══════════════════════════════════════════════════════
           PENDING REQUEST — TOTAL SEMUA MODUL
        ═══════════════════════════════════════════════════════ */
        $pendingProcurement  = (clone $procurementQuery)->where('status', 'pending')->count();
        $pendingConsumable   = (clone $consumableQuery)->where('status', 'pending')->count();
        $pendingCashAdvance  = 0;  // ← ganti kalau model Cash Advance sudah ada
        $pendingLiquidation  = 0;  // ← ganti kalau model Liquidation sudah ada
        $pendingReimbursement = 0; // ← ganti kalau model Reimbursement sudah ada

        // ✅ Total semua pending dari semua modul
        $pendingReq = $pendingProcurement
                    + $pendingConsumable
                    + $pendingCashAdvance
                    + $pendingLiquidation
                    + $pendingReimbursement;

        // ✅ Total semua yang approved dari semua modul
        $pendingApp = (clone $procurementQuery)->where('status', 'approved')->count()
                    + (clone $consumableQuery)->where('status', 'approved')->count();

        /* ═══════════════════════════════════════════════════════
           STATS UTAMA
        ═══════════════════════════════════════════════════════ */
        $stats = [
            'total_item'       => $totalItem,
            'total_stock'      => $totalStock,
            'low_stock'        => $lowStockCount,
            'total_vendor'     => $totalVendor,
            'pending_request'  => $pendingReq,   // ← total semua modul
            'pending_approval' => $pendingApp,
            'purchase_order'   => 0,
            'goods_received'   => 0,
        ];

        /* ═══════════════════════════════════════════════════════
           BREAKDOWN PER MODUL
        ═══════════════════════════════════════════════════════ */
        $moduleTotals = [
            // ── PROCUREMENT ──
            'procurement_total'    => (clone $procurementQuery)->count(),
            'procurement_pending'  => $pendingProcurement,
            'procurement_approved' => (clone $procurementQuery)->where('status', 'approved')->count(),
            'procurement_rejected' => (clone $procurementQuery)->where('status', 'rejected')->count(),

            // ── CONSUMABLE ──
            'consumable_total'     => (clone $consumableQuery)->count(),
            'consumable_pending'   => $pendingConsumable,
            'consumable_approved'  => (clone $consumableQuery)->where('status', 'approved')->count(),
            'consumable_rejected'  => (clone $consumableQuery)->where('status', 'rejected')->count(),

            // ── CASH ADVANCE ──
            'cash_advance_total'     => 0,
            'cash_advance_pending'   => $pendingCashAdvance,
            'cash_advance_approved'  => 0,
            'cash_advance_rejected'  => 0,

            // ── LIQUIDATION ──
            'liquidation_total'      => 0,
            'liquidation_pending'    => $pendingLiquidation,
            'liquidation_approved'   => 0,
            'liquidation_rejected'   => 0,

            // ── REIMBURSEMENT ──
            'reimbursement_total'     => 0,
            'reimbursement_pending'   => $pendingReimbursement,
            'reimbursement_approved'  => 0,
            'reimbursement_rejected'  => 0,
        ];

        /* ═══════════════════════════════════════════════════════
           KALAU MODEL CASH ADVANCE / LIQUIDATION / REIMBURSEMENT
           SUDAH ADA → UNCOMMENT & SESUAIKAN
        ═══════════════════════════════════════════════════════ */
        /*
        $cashAdvanceQuery   = \App\Models\CashAdvance::query();
        $liquidationQuery   = \App\Models\Liquidation::query();
        $reimbursementQuery = \App\Models\Reimbursement::query();

        if (!$isAdmin) {
            $cashAdvanceQuery->where('user_id', $user->id);
            $liquidationQuery->where('user_id', $user->id);
            $reimbursementQuery->where('user_id', $user->id);
        }

        $moduleTotals['cash_advance_total']     = (clone $cashAdvanceQuery)->count();
        $moduleTotals['cash_advance_pending']   = (clone $cashAdvanceQuery)->where('status', 'pending')->count();
        $moduleTotals['cash_advance_approved']  = (clone $cashAdvanceQuery)->where('status', 'approved')->count();
        $moduleTotals['cash_advance_rejected']  = (clone $cashAdvanceQuery)->where('status', 'rejected')->count();

        $moduleTotals['liquidation_total']      = (clone $liquidationQuery)->count();
        $moduleTotals['liquidation_pending']    = (clone $liquidationQuery)->where('status', 'pending')->count();
        $moduleTotals['liquidation_approved']   = (clone $liquidationQuery)->where('status', 'approved')->count();
        $moduleTotals['liquidation_rejected']   = (clone $liquidationQuery)->where('status', 'rejected')->count();

        $moduleTotals['reimbursement_total']     = (clone $reimbursementQuery)->count();
        $moduleTotals['reimbursement_pending']   = (clone $reimbursementQuery)->where('status', 'pending')->count();
        $moduleTotals['reimbursement_approved']  = (clone $reimbursementQuery)->where('status', 'approved')->count();
        $moduleTotals['reimbursement_rejected']  = (clone $reimbursementQuery)->where('status', 'rejected')->count();

        // Update total pending & approved di stats
        $stats['pending_request'] = $moduleTotals['procurement_pending']
                                  + $moduleTotals['consumable_pending']
                                  + $moduleTotals['cash_advance_pending']
                                  + $moduleTotals['liquidation_pending']
                                  + $moduleTotals['reimbursement_pending'];

        $stats['pending_approval'] = $moduleTotals['procurement_approved']
                                   + $moduleTotals['consumable_approved']
                                   + $moduleTotals['cash_advance_approved']
                                   + $moduleTotals['liquidation_approved']
                                   + $moduleTotals['reimbursement_approved'];
        */

        return view('dashboard.index', compact('stats', 'moduleTotals', 'lowStockItems'));
    }
}