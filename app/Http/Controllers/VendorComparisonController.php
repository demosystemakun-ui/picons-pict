<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\ProcurementVendor;
use App\Services\GeminiVisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class VendorComparisonController extends Controller
{
    public function __construct(
        private ?GeminiVisionService $ai = null
    ) {
        $this->ai = $this->ai ?? new GeminiVisionService();
    }

    /* ═══════════════════════════════════════════════════════════════
       INDEX — Detail Comparison per PR
    ═══════════════════════════════════════════════════════════════ */
    public function index(ProcurementRequest $procurement)
    {
        $user = auth()->user();

        if (!in_array($procurement->status, ['approved', 'processed', 'final_approved'])) {
            return redirect()
                ->route('procurement.show', $procurement)
                ->with('error', 'Vendor Comparison can only be accessed after the PR is approved.');
        }

        if ($user->hasRole('Admin Departemen') && $procurement->department_id !== $user->department_id) {
            abort(403, 'You do not have access to vendor comparisons from other departments.');
        }

        $procurement->load('vendors.items', 'winnerVendor');

        return view('procurement.comparison.index', compact('procurement'));
    }

    /* ═══════════════════════════════════════════════════════════════
       GLOBAL INDEX — Daftar semua PR yang memiliki comparison
    ═══════════════════════════════════════════════════════════════ */
    public function globalIndex()
    {
        $procurements = ProcurementRequest::whereHas('vendors')
            ->with(['vendors', 'winnerVendor', 'department'])
            ->latest()
            ->paginate(15);

        return view('procurement.comparison.global-index', compact('procurements'));
    }

    /* ═══════════════════════════════════════════════════════════════
       CREATE FINAL PR — Form untuk melengkapi data PR Final
    ═══════════════════════════════════════════════════════════════ */
    public function createFinalPr(ProcurementRequest $procurement)
    {
        // Pastikan sudah ada vendor pemenang
        if (!$procurement->winnerVendor) {
            return redirect()
                ->route('procurement.comparisons.index', $procurement)
                ->with('error', 'Please select a winning vendor first.');
        }

        // Idempotent: kalau sudah final, langsung ke PDF
        if ($procurement->status === 'final_approved' && $procurement->final_number) {
            return redirect()
                ->route('procurement.pdf.final', $procurement)
                ->with('info', 'Final PR already exists. Redirecting to PDF.');
        }

        $procurement->load('winnerVendor.items', 'department');

        // ═══ GENERATE FORMAT OTOMATIS ═══
        $bulanRomawi = $this->getRomawi((int) date('n'));
        $tahun       = date('Y');
        
        // Mengambil kode PR awal, contoh: OPS-PICT
        $prCode = $procurement->no ?? 'OPS-PICT'; 
        
        // Format: 07/PR/OPS-PICT/XI-2026
        $suggestedFinalNumber = '07/PR/' . $prCode . '/' . $bulanRomawi . '-' . $tahun;

        return view('procurement.comparison.create-final-pr', compact('procurement', 'suggestedFinalNumber'));
    }

    /* ═══════════════════════════════════════════════════════════════
       STORE FINAL PR — Simpan data PR Final & redirect ke PDF
    ═══════════════════════════════════════════════════════════════ */
    public function storeFinalPr(Request $request, ProcurementRequest $procurement)
    {
        // Guard: winner harus masih ada
        if (!$procurement->winnerVendor) {
            return back()->with('error', 'Winning vendor is missing. Please select winner again.');
        }

        // Validasi menggunakan field 'final_number' biasa
        $validated = $request->validate([
            'final_number'         => 'required|string|max:100|unique:procurement_requests,final_number,' . $procurement->id,
            'budget_type'          => 'required|in:Budgeted,Non Budgeted',
            'final_description'    => 'required|string|max:255',
            'reason_choose_vendor' => 'required|string|max:2000',
            'accounting_mgr_name'  => 'nullable|string|max:255',
            'cfo_name'             => 'nullable|string|max:255',
            'ceo_name'             => 'nullable|string|max:255',
        ]);

        $procurement->update([
            'budget_type'          => $validated['budget_type'],
            'description'          => $validated['final_description'],
            'final_number'         => $validated['final_number'],
            'reason_choose_vendor' => $validated['reason_choose_vendor'],
            'accounting_mgr_name'  => $validated['accounting_mgr_name'] ?? null,
            'cfo_name'             => $validated['cfo_name'] ?? null,
            'ceo_name'             => $validated['ceo_name'] ?? null,
            'final_total_price'    => $procurement->winnerVendor->total_price,
            'status'               => 'final_approved',
        ]);

        // Redirect langsung ke PDF Final
        return redirect()
            ->route('procurement.pdf.final', $procurement)
            ->with('success', 'Final PR saved. PDF is ready.');
    }

    /* ═══════════════════════════════════════════════════════════════
       SCAN — 3 entry point
    ═══════════════════════════════════════════════════════════════ */
    public function scanMonotaro(Request $request, ProcurementRequest $procurement)
    {
        return $this->processScan($request, $procurement, 'monotaro');
    }

    public function scanOthers(Request $request, ProcurementRequest $procurement)
    {
        return $this->processScan($request, $procurement, 'others');
    }

    public function scanImage(Request $request, ProcurementRequest $procurement)
    {
        return $this->processScan($request, $procurement, 'auto');
    }

    /* ═══════════════════════════════════════════════════════════════
       PROCESS SCAN
    ═══════════════════════════════════════════════════════════════ */
    private function processScan(Request $request, ProcurementRequest $procurement, string $mode)
    {
        $request->validate([
            'screenshots'   => 'required|array|min:1',
            'screenshots.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        try {
            $extracted = $this->ai->extractVendors($request->file('screenshots'), $mode);

            if (empty($extracted)) {
                return back()->with('error', 'AI did not detect vendor data from the screenshots.');
            }

            $groupedVendors = [];

            foreach ($extracted as $item) {
                $marketplace = strtolower(trim($item['marketplace'] ?? ''));
                if (empty($marketplace) || $marketplace === 'auto' || $marketplace === 'others') {
                    $marketplace = strtolower($item['vendor_name'] ?? ($mode === 'monotaro' ? 'Monotaro' : 'Others'));
                }

                if (!isset($groupedVendors[$marketplace])) {
                    $groupedVendors[$marketplace] = [
                        'vendor_name'      => ucfirst($marketplace),
                        'marketplace'      => ucfirst($marketplace),
                        'items'            => [],
                        'shipping_cost'    => 0,
                        'discount_voucher' => 0,
                        'tax_ppn'          => 0,
                    ];
                }

                if (!empty($item['items']) && is_array($item['items'])) {
                    foreach ($item['items'] as $it) {
                        $exists = false;
                        foreach ($groupedVendors[$marketplace]['items'] as &$existingItem) {
                            $isSameSku  = !empty($it['sku']) && $existingItem['sku'] === $it['sku'];
                            $isSameName = empty($it['sku']) && strtolower($existingItem['product_name']) === strtolower($it['product_name']);

                            if ($isSameSku || $isSameName) {
                                $existingItem['quantity'] += ($it['quantity'] ?? 1);
                                $existingItem['subtotal'] += ($it['subtotal'] ?? 0);
                                $exists = true;
                                break;
                            }
                        }
                        unset($existingItem);

                        if (!$exists) {
                            $groupedVendors[$marketplace]['items'][] = $it;
                        }
                    }
                }

                $groupedVendors[$marketplace]['shipping_cost']    += ($item['shipping_cost'] ?? 0);
                $groupedVendors[$marketplace]['discount_voucher'] += ($item['discount_voucher'] ?? 0);
                $groupedVendors[$marketplace]['tax_ppn']          += ($item['tax_ppn'] ?? 0);
            }

            foreach ($groupedVendors as $marketplaceName => $data) {
                $itemsSubtotal  = collect($data['items'])->sum('subtotal');
                $totalBeforeTax = $itemsSubtotal + $data['shipping_cost'] - $data['discount_voucher'];
                $totalPrice     = $totalBeforeTax + $data['tax_ppn'];

                if ($mode !== 'others') {
                    $procurement->vendors()->where('marketplace', $data['marketplace'])->delete();
                }

                $existingVendor = null;
                if ($mode === 'others') {
                    $existingVendor = $procurement->vendors()->where('marketplace', $data['marketplace'])->first();
                }

                if ($existingVendor) {
                    foreach ($data['items'] as $newItem) {
                        $existingItem = $existingVendor->items()
                            ->where(function ($q) use ($newItem) {
                                if (!empty($newItem['sku'])) {
                                    $q->where('sku', $newItem['sku']);
                                } else {
                                    $q->where('product_name', $newItem['product_name']);
                                }
                            })->first();

                        if ($existingItem) {
                            $existingItem->update([
                                'quantity' => $existingItem->quantity + ($newItem['quantity'] ?? 1),
                                'subtotal' => $existingItem->subtotal + ($newItem['subtotal'] ?? 0),
                            ]);
                        } else {
                            $existingVendor->items()->create([
                                'product_name' => $newItem['product_name'] ?? '-',
                                'variant'      => $newItem['variant']      ?? null,
                                'sku'          => $newItem['sku']          ?? null,
                                'image_url'    => $newItem['image_url']    ?? null,
                                'quantity'     => $newItem['quantity']     ?? 1,
                                'unit_price'   => $newItem['unit_price']   ?? 0,
                                'subtotal'     => $newItem['subtotal']     ?? 0,
                                'stock_status' => $newItem['stock_status'] ?? null,
                                'weight'       => $newItem['weight']       ?? null,
                                'sort_order'   => $existingVendor->items()->count() + 1,
                            ]);
                        }
                    }

                    $newSubtotal       = $existingVendor->items()->sum('subtotal');
                    $newTotalBeforeTax = $newSubtotal + $existingVendor->shipping_cost - $existingVendor->discount_voucher;
                    $existingVendor->update([
                        'subtotal'         => $newSubtotal,
                        'total_before_tax' => $newTotalBeforeTax,
                        'total_price'      => $newTotalBeforeTax + $existingVendor->tax_ppn,
                    ]);
                } else {
                    $vendor = $procurement->vendors()->create([
                        'vendor_name'      => $data['vendor_name'],
                        'marketplace'      => $data['marketplace'],
                        'subtotal'         => $itemsSubtotal,
                        'shipping_cost'    => $data['shipping_cost'],
                        'discount_voucher' => $data['discount_voucher'],
                        'tax_ppn'          => $data['tax_ppn'],
                        'total_before_tax' => $totalBeforeTax,
                        'total_price'      => $totalPrice,
                        'sort_order'       => 1,
                    ]);

                    foreach ($data['items'] as $j => $item) {
                        $vendor->items()->create([
                            'product_name' => $item['product_name'] ?? '-',
                            'variant'      => $item['variant']      ?? null,
                            'sku'          => $item['sku']          ?? null,
                            'image_url'    => $item['image_url']    ?? null,
                            'quantity'     => $item['quantity']     ?? 1,
                            'unit_price'   => $item['unit_price']   ?? 0,
                            'subtotal'     => $item['subtotal']     ?? 0,
                            'stock_status' => $item['stock_status'] ?? null,
                            'weight'       => $item['weight']       ?? null,
                            'sort_order'   => $j + 1,
                        ]);
                    }
                }
            }

            $this->autoPickWinner($procurement);

            $message = count($groupedVendors) . ' marketplace(s) successfully scanned and merged into cart.';

            return redirect()
                ->route('procurement.comparisons.index', $procurement)
                ->with('success', $message);

        } catch (Throwable $e) {
            Log::error('AI Scan Error: ' . $e->getMessage(), [
                'procurement_id' => $procurement->id,
                'mode'           => $mode,
            ]);

            return back()->with('error', 'Failed to scan screenshot: ' . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       STORE — Simpan / update data vendor
    ═══════════════════════════════════════════════════════════════ */
    public function store(Request $request, ProcurementRequest $procurement)
    {
        $validated = $request->validate([
            'budget_type'                      => 'nullable|in:Budgeted,Non Budgeted',
            'vendors'                          => 'required|array|min:1',
            'vendors.*.vendor_name'            => 'required|string|max:255',
            'vendors.*.marketplace'            => 'nullable|string|max:100',
            'vendors.*.company_name'           => 'nullable|string|max:255',
            'vendors.*.payment_method'         => 'nullable|string|max:255',
            'vendors.*.payment_bank'           => 'nullable|string|max:255',
            'vendors.*.estimated_delivery'     => 'nullable|string|max:255',
            'vendors.*.product_url'            => 'nullable|string',
            'vendors.*.notes'                  => 'nullable|string',
            'vendors.*.subtotal'               => 'nullable|numeric|min:0',
            'vendors.*.shipping_cost'          => 'nullable|numeric|min:0',
            'vendors.*.discount_voucher'       => 'nullable|numeric|min:0',
            'vendors.*.tax_ppn'                => 'nullable|numeric|min:0',
            'vendors.*.total_before_tax'       => 'nullable|numeric|min:0',
            'vendors.*.total_price'            => 'required|numeric|min:0',
            'vendors.*.tax_note'               => 'nullable|string|max:50',

            'vendors.*.items'                       => 'nullable|array',
            'vendors.*.items.*.product_name'        => 'required_with:vendors.*.items|string|max:255',
            'vendors.*.items.*.variant'             => 'nullable|string|max:255',
            'vendors.*.items.*.sku'                 => 'nullable|string|max:100',
            'vendors.*.items.*.quantity'            => 'required_with:vendors.*.items|numeric|min:0',
            'vendors.*.items.*.unit_price'          => 'required_with:vendors.*.items|numeric|min:0',
            'vendors.*.items.*.subtotal'            => 'nullable|numeric|min:0',
            'vendors.*.items.*.stock_status'        => 'nullable|string|max:50',
            'vendors.*.items.*.weight'              => 'nullable|numeric|min:0',
        ]);

        $procurement->update([
            'budget_type' => $validated['budget_type'] ?? null,
        ]);

        $procurement->vendors()->delete();

        foreach ($validated['vendors'] as $i => $v) {
            $vendor = $procurement->vendors()->create([
                'vendor_name'        => $v['vendor_name'],
                'marketplace'        => $v['marketplace'] ?? null,
                'company_name'       => $v['company_name'] ?? null,
                'payment_method'     => $v['payment_method'] ?? null,
                'payment_bank'       => $v['payment_bank'] ?? null,
                'estimated_delivery' => $v['estimated_delivery'] ?? null,
                'product_url'        => $v['product_url'] ?? null,
                'notes'              => $v['notes'] ?? null,
                'subtotal'           => $v['subtotal'] ?? 0,
                'shipping_cost'      => $v['shipping_cost'] ?? 0,
                'discount_voucher'   => $v['discount_voucher'] ?? 0,
                'tax_ppn'            => $v['tax_ppn'] ?? 0,
                'total_before_tax'   => $v['total_before_tax'] ?? 0,
                'total_price'        => $v['total_price'],
                'tax_note'           => $v['tax_note'] ?? null,
                'sort_order'         => $i + 1,
            ]);

            foreach ($v['items'] ?? [] as $j => $item) {
                $vendor->items()->create([
                    'product_name' => $item['product_name'],
                    'variant'      => $item['variant'] ?? null,
                    'sku'          => $item['sku'] ?? null,
                    'quantity'     => $item['quantity'] ?? 1,
                    'unit_price'   => $item['unit_price'] ?? 0,
                    'subtotal'     => $item['subtotal'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0)),
                    'stock_status' => $item['stock_status'] ?? null,
                    'weight'       => $item['weight'] ?? null,
                    'sort_order'   => $j + 1,
                ]);
            }
        }

        $this->autoPickWinner($procurement);

        return redirect()
            ->route('procurement.comparisons.index', $procurement)
            ->with('success', 'Vendor data saved successfully.');
    }

    /* ═══════════════════════════════════════════════════════════════
       SELECT VENDOR — Manual pick winner
    ═══════════════════════════════════════════════════════════════ */
    public function selectVendor(ProcurementRequest $procurement, ProcurementVendor $vendor)
    {
        // Pastikan vendor milik procurement ini
        if ($vendor->procurement_request_id !== $procurement->id) {
            abort(404);
        }

        // Hanya boleh ganti winner kalau status masih 'approved'
        if ($procurement->status !== 'approved') {
            return back()->with('error', 'Winner can only be changed while status is Approved.');
        }

        // Reset semua winner
        $procurement->vendors()->update(['is_winner' => false]);

        // Set yang baru
        $vendor->update(['is_winner' => true]);

        $procurement->update([
            'final_total_price' => $vendor->total_price,
        ]);

        return back()->with('success', 'Winning vendor updated to: ' . $vendor->vendor_name);
    }

    /* ═══════════════════════════════════════════════════════════════
       DESTROY
    ═══════════════════════════════════════════════════════════════ */
    public function destroy(ProcurementVendor $vendor)
    {
        $procurement = $vendor->procurementRequest;
        $vendor->delete();

        $this->autoPickWinner($procurement);

        return back()->with('success', 'Vendor deleted successfully.');
    }

    /* ═══════════════════════════════════════════════════════════════
       AUTO-PICK WINNER
    ═══════════════════════════════════════════════════════════════ */
    private function autoPickWinner(ProcurementRequest $procurement): void
    {
        $procurement->vendors()->update(['is_winner' => false]);

        $winner = $procurement->vendors()
            ->where('total_price', '>', 0)
            ->orderBy('total_price')
            ->first();

        if ($winner) {
            $winner->update(['is_winner' => true]);
        }

        $procurement->update([
            'final_total_price' => $winner?->total_price,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════════
       HELPER: Konversi Bulan ke Romawi
    ═══════════════════════════════════════════════════════════════ */
    private function getRomawi($number) {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $map[$number] ?? '';
    }
}