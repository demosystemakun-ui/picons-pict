<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\ProcurementRequest;
use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Default filter: Bulan ini
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));

        /* ═══════════════════════════════════════════════════════
           1. BARANG PALING SERING DIBELI (Berdasarkan PO)
        ═══════════════════════════════════════════════════════ */
        $topItems = PurchaseItem::query()
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->whereDate('purchases.order_date', '>=', $startDate)
            ->whereDate('purchases.order_date', '<=', $endDate)
            ->select(
                'purchase_items.product_name',
                DB::raw('SUM(purchase_items.qty_ordered) as total_qty'),
                DB::raw('SUM(purchase_items.qty_ordered * purchase_items.unit_price) as total_spent')
            )
            ->groupBy('purchase_items.product_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        /* ═══════════════════════════════════════════════════════
           2. VENDOR PALING AKTIF (Berdasarkan PO)
        ═══════════════════════════════════════════════════════ */
        $topVendors = Purchase::query()
            ->join('procurement_vendors', 'purchases.vendor_id', '=', 'procurement_vendors.id')
            ->whereDate('purchases.order_date', '>=', $startDate)
            ->whereDate('purchases.order_date', '<=', $endDate)
            ->select(
                'procurement_vendors.vendor_name',
                DB::raw('COUNT(purchases.id) as total_po'),
                DB::raw('SUM(purchases.final_total_price) as total_spent') // Asumsi ada kolom total di purchases
            )
            ->groupBy('procurement_vendors.vendor_name')
            ->orderByDesc('total_spent')
            ->take(10)
            ->get();

        return view('reports.index', compact('topItems', 'topVendors', 'startDate', 'endDate'));
    }
}