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

    /**
     * Scan khusus Monotaro.
     */
    public function scanMonotaro(Request $request, ProcurementRequest $procurement)
    {
        return $this->processScan($request, $procurement, 'monotaro');
    }

    /**
     * Scan untuk marketplace lain (Shopee, Tokopedia, Lazada, Bukalapak, Blibli).
     */
    public function scanOthers(Request $request, ProcurementRequest $procurement)
    {
        return $this->processScan($request, $procurement, 'others');
    }

    /**
     * Scan generic (auto-detect semua marketplace).
     */
    public function scanImage(Request $request, ProcurementRequest $procurement)
    {
        return $this->processScan($request, $procurement, 'auto');
    }

    /* ═══════════════════════════════════════════════════════════════
       PROCESS SCAN — Shared logic
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

            /* ═══ Mode handling ═══
               - 'monotaro' → REPLACE semua vendor (reset)
               - 'others'   → APPEND (tambah ke vendor existing)
               - 'auto'     → REPLACE semua
            */
            if ($mode === 'others') {
                // Append mode: hindari duplikasi nama vendor
                $existingNames = $procurement->vendors()
                    ->pluck('vendor_name')
                    ->map(fn ($n) => strtolower(trim($n)))
                    ->toArray();

                $extracted = array_filter(
                    $extracted,
                    fn ($v) => !in_array(strtolower(trim($v['vendor_name'] ?? '')), $existingNames)
                );

                $extracted = array_values($extracted);

                if (empty($extracted)) {
                    return back()->with('warning', 'Semua vendor dari screenshot sudah ada di daftar.');
                }

                $baseSort = ($procurement->vendors()->max('sort_order') ?? 0);
            } else {
                // Replace mode
                $procurement->vendors()->delete();
                $baseSort = 0;
            }

            /* ═══ Simpan vendor + items ═══ */
            foreach ($extracted as $i => $v) {
                $vendor = $procurement->vendors()->create([
                    'vendor_name'        => $v['vendor_name']        ?? 'Vendor ' . ($i + 1),
                    'marketplace'        => $v['marketplace']        ?? ($mode === 'monotaro' ? 'Monotaro' : null),
                    'company_name'       => $v['company_name']       ?? null,
                    'payment_method'     => $v['payment_method']     ?? null,
                    'payment_bank'       => $v['payment_bank']       ?? null,
                    'estimated_delivery' => $v['estimated_delivery'] ?? null,
                    'product_url'        => $v['store_url']          ?? null,
                    'notes'              => $v['notes']              ?? null,
                    'subtotal'           => $v['subtotal']           ?? 0,
                    'shipping_cost'      => $v['shipping_cost']      ?? 0,
                    'discount_voucher'   => $v['discount_voucher']   ?? 0,
                    'tax_ppn'            => $v['tax_ppn']            ?? 0,
                    'total_before_tax'   => $v['total_before_tax']   ?? 0,
                    'total_price'        => $v['total_price']        ?? 0,
                    'tax_note'           => $v['tax_note']           ?? 'Include Tax',
                    'sort_order'         => $baseSort + $i + 1,
                ]);

                if (!empty($v['items']) && is_array($v['items'])) {
                    foreach ($v['items'] as $j => $item) {
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

            $modeLabel = match ($mode) {
                'monotaro' => 'Monotaro',
                'others'   => 'Marketplace lain',
                default    => '',
            };

            $message = count($extracted) . ' vendor';
            if ($modeLabel) $message .= " ({$modeLabel})";
            $message .= ' berhasil di-scan.';

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
        $validated = $request->validate([
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