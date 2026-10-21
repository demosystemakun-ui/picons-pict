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
       INDEX — Halaman comparison
    ═══════════════════════════════════════════════════════════════ */
    public function index(ProcurementRequest $procurement)
    {
        $procurement->load('vendors.items');
        return view('procurement.comparison.create', compact('procurement'));
    }

    /* ═══════════════════════════════════════════════════════════════
       SCAN — 3 entry point (Monotaro / Others / Generic)
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
       PROCESS SCAN — Shared logic (REVISED: Grouping & Merging)
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
                return back()->with('error', 'AI tidak mendeteksi data vendor dari screenshot.');
            }

            // 1. Grouping data hasil scan berdasarkan Marketplace (Shopee, Monotaro, dll)
            $groupedVendors = [];
            
            foreach ($extracted as $item) {
                // Tentukan nama marketplace (fallback ke mode atau vendor_name)
                $marketplace = strtolower(trim($item['marketplace'] ?? ''));
                if (empty($marketplace) || $marketplace === 'auto' || $marketplace === 'others') {
                    $marketplace = strtolower($item['vendor_name'] ?? ($mode === 'monotaro' ? 'Monotaro' : 'Lainnya'));
                }

                // Inisialisasi array untuk marketplace jika belum ada
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

                // Gabungkan items & cek duplikasi
                if (!empty($item['items']) && is_array($item['items'])) {
                    foreach ($item['items'] as $it) {
                        $exists = false;
                        // Cek apakah produk sudah ada di keranjang marketplace ini
                        foreach ($groupedVendors[$marketplace]['items'] as &$existingItem) {
                            $isSameSku = !empty($it['sku']) && $existingItem['sku'] === $it['sku'];
                            $isSameName = empty($it['sku']) && strtolower($existingItem['product_name']) === strtolower($it['product_name']);
                            
                            if ($isSameSku || $isSameName) {
                                // Jika sama, tambahkan quantity dan subtotal
                                $existingItem['quantity'] += ($it['quantity'] ?? 1);
                                $existingItem['subtotal'] += ($it['subtotal'] ?? 0);
                                $exists = true;
                                break;
                            }
                        }
                        unset($existingItem); // Hapus referensi agar aman

                        // Jika tidak duplikat, masukkan sebagai item baru
                        if (!$exists) {
                            $groupedVendors[$marketplace]['items'][] = $it;
                        }
                    }
                }

                // Akumulasi biaya tambahan
                $groupedVendors[$marketplace]['shipping_cost'] += ($item['shipping_cost'] ?? 0);
                $groupedVendors[$marketplace]['discount_voucher'] += ($item['discount_voucher'] ?? 0);
                $groupedVendors[$marketplace]['tax_ppn'] += ($item['tax_ppn'] ?? 0);
            }

            // 2. Simpan Vendor yang sudah di-group ke Database
            foreach ($groupedVendors as $marketplaceName => $data) {
                // Hitung ulang total untuk vendor ini
                $itemsSubtotal = collect($data['items'])->sum('subtotal');
                $totalBeforeTax = $itemsSubtotal + $data['shipping_cost'] - $data['discount_voucher'];
                $totalPrice = $totalBeforeTax + $data['tax_ppn'];

                // Jika mode REPLACE (Monotaro/Auto), hapus data lama untuk marketplace ini
                if ($mode !== 'others') {
                    $procurement->vendors()->where('marketplace', $data['marketplace'])->delete();
                }

                // Cek apakah vendor untuk marketplace ini sudah ada (khusus mode append/others)
                $existingVendor = null;
                if ($mode === 'others') {
                    $existingVendor = $procurement->vendors()->where('marketplace', $data['marketplace'])->first();
                }

                if ($existingVendor) {
                    // UPDATE: Tambahkan item baru ke vendor yang sudah ada
                    foreach ($data['items'] as $newItem) {
                        $existingItem = $existingVendor->items()
                            ->where(function($q) use ($newItem) {
                                if (!empty($newItem['sku'])) {
                                    $q->where('sku', $newItem['sku']);
                                } else {
                                    $q->where('product_name', $newItem['product_name']);
                                }
                            })->first();

                        if ($existingItem) {
                            // Update quantity jika item sudah ada
                            $existingItem->update([
                                'quantity' => $existingItem->quantity + ($newItem['quantity'] ?? 1),
                                'subtotal' => $existingItem->subtotal + ($newItem['subtotal'] ?? 0)
                            ]);
                        } else {
                            // Buat item baru
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
                    
                    // Update totalan vendor
                    $newSubtotal = $existingVendor->items()->sum('subtotal');
                    $newTotalBeforeTax = $newSubtotal + $existingVendor->shipping_cost - $existingVendor->discount_voucher;
                    $existingVendor->update([
                        'subtotal'         => $newSubtotal,
                        'total_before_tax' => $newTotalBeforeTax,
                        'total_price'      => $newTotalBeforeTax + $existingVendor->tax_ppn
                    ]);

                } else {
                    // CREATE: Buat vendor baru (Keranjang baru)
                    $vendor = $procurement->vendors()->create([
                        'vendor_name'        => $data['vendor_name'],
                        'marketplace'        => $data['marketplace'],
                        'subtotal'           => $itemsSubtotal,
                        'shipping_cost'      => $data['shipping_cost'],
                        'discount_voucher'   => $data['discount_voucher'],
                        'tax_ppn'            => $data['tax_ppn'],
                        'total_before_tax'   => $totalBeforeTax,
                        'total_price'        => $totalPrice,
                        'sort_order'         => 1, // Bisa disesuaikan
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

            $message = count($groupedVendors) . ' marketplace berhasil di-scan dan digabungkan ke keranjang.';

            return redirect()
                ->route('procurement.comparisons.index', $procurement)
                ->with('success', $message);

        } catch (Throwable $e) {
            Log::error('AI Scan Error: ' . $e->getMessage(), [
                'procurement_id' => $procurement->id,
                'mode'           => $mode,
            ]);

            return back()->with('error', 'Gagal scan screenshot: ' . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       STORE — Simpan / update data vendor (dari form manual)
    ═══════════════════════════════════════════════════════════════ */
    public function store(Request $request, ProcurementRequest $procurement)
    {
        // 1. TAMBAHKAN VALIDASI UNTUK BUDGET_TYPE
        $validated = $request->validate([
            'budget_type'                      => 'nullable|in:Budgeted,Non Budgeted', // <-- TAMBAHKAN INI
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

        // 2. SIMPAN BUDGET_TYPE KE DATABASE
        $procurement->update([
            'budget_type' => $validated['budget_type'] ?? null // <-- TAMBAHKAN INI
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
            ->with('success', 'Data vendor berhasil disimpan.');
    }

    /* ═══════════════════════════════════════════════════════════════
       DESTROY — Hapus 1 vendor
    ═══════════════════════════════════════════════════════════════ */
    public function destroy(ProcurementVendor $vendor)
    {
        $procurement = $vendor->procurementRequest;
        $vendor->delete();

        $this->autoPickWinner($procurement);

        return back()->with('success', 'Vendor berhasil dihapus.');
    }

    /* ═══════════════════════════════════════════════════════════════
       AUTO-PICK WINNER — Vendor dengan total_price terkecil
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
}