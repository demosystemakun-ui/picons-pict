<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\ProcurementVendor;
use App\Models\CashAdvance;
use App\Services\GeminiVisionService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Throwable;

class VendorComparisonController extends Controller
{
    public function __construct(
        private ?GeminiVisionService $ai = null
    ) {
        $this->ai = $this->ai ?? new GeminiVisionService();
    }

    public function index(ProcurementRequest $procurement)
    {
        $user = auth()->user();

        if (!in_array($procurement->status, ['approved', 'processed', 'pending_review_final', 'pending_final_sign', 'final_approved'])) {
            return redirect()
                ->route('procurement.show', ['procurementRequest' => $procurement->id])
                ->with('error', 'Vendor Comparison can only be accessed after the PR is approved.');
        }

        // ═══ LOGIKA FILTER DATA SESUAI ROLE ═══
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurement->department_id !== $user->department_id) {
                    abort(403, 'You do not have access to vendor comparisons from other departments.');
                }
            } else {
                if ($procurement->user_id !== $user->id) {
                    abort(403, 'You do not have access to this vendor comparison.');
                }
            }
        }
        // ═════════════════════════════════════════

        $procurement->load('vendors.items', 'winnerVendor');

        return view('procurement.comparison.index', compact('procurement'));
    }

    public function globalIndex()
    {
        $user = auth()->user();

        $procurements = ProcurementRequest::whereHas('vendors')
            ->with(['vendors', 'winnerVendor', 'department'])
            // ═══ LOGIKA FILTER DATA SESUAI ROLE ═══
            ->when(!$user->hasAnyRole(['Super Admin', 'Admin']), function ($q) use ($user) {
                if ($user->hasRole('Admin Departemen')) {
                    $q->where('department_id', $user->department_id);
                } else {
                    $q->where('user_id', $user->id);
                }
            })
            // ═════════════════════════════════════════
            ->latest()
            ->paginate(15);

        return view('procurement.comparison.global-index', compact('procurements'));
    }

    public function createFinalPr(ProcurementRequest $procurement)
    {
        $user = auth()->user();

        // ═══ LOGIKA FILTER DATA SESUAI ROLE ═══
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurement->department_id !== $user->department_id) {
                    abort(403, 'You do not have access to this PR.');
                }
            } else {
                if ($procurement->user_id !== $user->id) {
                    abort(403, 'You do not have access to this PR.');
                }
            }
        }
        // ═════════════════════════════════════════

        if (!$procurement->winnerVendor) {
            return redirect()
                ->route('procurement.comparisons.index', ['procurement' => $procurement->id])
                ->with('error', 'Please select a winning vendor first.');
        }

        if (in_array($procurement->status, ['final_approved']) && $procurement->final_number) {
            return redirect()
                ->route('procurement.pdf.final', ['procurementRequest' => $procurement->id])
                ->with('info', 'Final PR already exists and validated.');
        }

        $procurement->load('winnerVendor.items', 'department');

        $suggestedFinalNumber = $this->generateFinalNumber($procurement);

        return view('procurement.comparison.create-final-pr', compact('procurement', 'suggestedFinalNumber'));
    }

    private function generateFinalNumber(ProcurementRequest $procurement): string
    {
        $bulanRomawi = $this->getRomawi((int) date('n'));
        $tahun       = date('Y');
        $prCode      = $procurement->no ? preg_replace('/^[0-9]+\/PR\//', '', $procurement->no) : 'OPS/PICT';

        $lastPr = ProcurementRequest::whereNotNull('final_number')
            ->where('final_number', 'like', "%/PR/{$prCode}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastPr && $lastPr->final_number) {
            $lastNumber = (int) substr($lastPr->final_number, 0, 2);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $formattedNumber = str_pad($nextNumber, 2, '0', STR_PAD_LEFT);

        return $formattedNumber . '/PR/' . $prCode . '-' . $bulanRomawi . '-' . $tahun;
    }

    public function storeFinalPr(Request $request, ProcurementRequest $procurement)
    {
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['Super Admin', 'Admin']);

        // ═══ LOGIKA FILTER DATA SESUAI ROLE ═══
        if (!$isAdmin) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurement->department_id !== $user->department_id) {
                    abort(403, 'You do not have access to this PR.');
                }
            } else {
                if ($procurement->user_id !== $user->id) {
                    abort(403, 'You do not have access to this PR.');
                }
            }
        }
        // ═════════════════════════════════════════

        if (!$procurement->winnerVendor) {
            return back()->with('error', 'Winning vendor is missing. Please select winner again.');
        }

        // ═══ ATURAN VALIDASI ═══
        $rules = [
            'budget_type'          => 'required|in:Budgeted,Non Budgeted',
            'final_description'    => 'required|string|max:255',
            'reason_choose_vendor' => 'required|string|max:2000',
            'request_date'         => 'required|date',
            'request_by'           => 'required|string|max:255',
            'division'             => 'required|string|max:255',
            'prepared_by'          => 'required|string|max:255',
            'accounting_mgr_name'  => 'nullable|string|max:255',
            'cfo_name'             => 'nullable|string|max:255',
            'ceo_name'             => 'nullable|string|max:255',
        ];

        // Hanya Admin yang wajib mengisi final_number
        if ($isAdmin) {
            $rules['final_number'] = [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($procurement) {
                    $existsInFinal = \App\Models\ProcurementRequest::where('final_number', $value)
                        ->where('id', '!=', $procurement->id)
                        ->exists();
                    
                    $existsInNo = \App\Models\ProcurementRequest::where('no', $value)->exists();

                    if ($existsInFinal || $existsInNo) {
                        $fail('Nomor Final PR ini sudah digunakan (baik sebagai PR Awal maupun PR Final). Silakan gunakan nomor lain.');
                    }
                },
            ];
        }
        // ══════════════════════════

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            $updateData = [
                'budget_type'          => $validated['budget_type'],
                'description'          => $validated['final_description'],
                'reason_choose_vendor' => $validated['reason_choose_vendor'],
                'request_date'         => $validated['request_date'],
                'request_by'           => $validated['request_by'],
                'division'             => $validated['division'],
                'prepared_by'          => $validated['prepared_by'],
                'accounting_mgr_name'  => $validated['accounting_mgr_name'] ?? null,
                'cfo_name'             => $validated['cfo_name'] ?? null,
                'ceo_name'             => $validated['ceo_name'] ?? null,
                'final_total_price'    => $procurement->winnerVendor->total_price,
                'status'               => 'pending_review_final',
            ];

            // Simpan nomor hanya jika diinput oleh Admin
            if ($isAdmin && !empty($validated['final_number'])) {
                $updateData['final_number'] = $validated['final_number'];
            }

            $procurement->update($updateData);

            if ($procurement->procurement_method === 'cash_advance') {
                $existingCa = CashAdvance::where('procurement_request_id', $procurement->id)->first();

                if (!$existingCa) {
                    $ca = CashAdvance::create([
                        'procurement_request_id' => $procurement->id,
                        'number'                 => 'DRAFT-CA-' . date('Ymd') . '-' . $procurement->id,
                        'request_date'           => now(),
                        'requested_by'           => $validated['request_by'],
                        'department'             => $procurement->department->name ?? '-',
                        'cost_department'        => $procurement->department->name ?? '-',
                        'status'                 => 'pending',
                        'notes'                  => 'Otomatis dibuat dari PR: ' . ($procurement->final_number ?? 'DRAFT'),
                    ]);

                    if ($procurement->winnerVendor) {
                        foreach ($procurement->winnerVendor->items as $item) {
                            $ca->items()->create([
                                'item_description' => $item->product_name,
                                'qty'              => $item->quantity,
                                'unit'             => 'Pcs',
                                'price'            => $item->unit_price,
                                'purpose'          => 'Pembelian dari vendor ' . $procurement->winnerVendor->vendor_name,
                            ]);
                        }
                    }
                }
            }

            // ═══ TRIGGER WA KE ADMIN & USER (PR Final baru dibuat) ═══
            $this->notifyAdminsFinalPrCreated($procurement);
            $this->notifyUserFinalPrCreated($procurement);
            // ═══════════════════════════════════════════════════════════

            DB::commit();

            return redirect()
                ->route('procurement.comparisons.index', ['procurement' => $procurement->id])
                ->with('success', 'Final PR saved. Waiting for Admin format review.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Store Final PR Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to save Final PR: ' . $e->getMessage());
        }
    }

    /**
     * ═══ TRIGGER WA: Notifikasi ke Admin saat Final PR baru dibuat ═══
     */
    private function notifyAdminsFinalPrCreated(ProcurementRequest $procurement): void
    {
        try {
            $wa = new WhatsAppService();
            $prNo = $procurement->final_number ?? $procurement->no;

            $admins = \App\Models\User::role(['Super Admin', 'Admin'])->get();

            foreach ($admins as $admin) {
                if (!empty($admin->phone_number)) {
                    $wa->send(
                        $admin->phone_number,
                        "Halo {$admin->name}, ada *PR Final baru* menunggu review format:\n\nNo: {$prNo}\nDept: " . ($procurement->division ?? '-') . "\nTotal: Rp " . number_format((float) $procurement->final_total_price, 0, ',', '.') . "\n\nSilakan cek di sistem."
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsApp notify admins failed for PR #' . $procurement->id . ': ' . $e->getMessage());
        }
    }

    /**
     * ═══ TRIGGER WA: Notifikasi ke User (Pembuat PR) saat Final PR dibuat ═══
     */
    private function notifyUserFinalPrCreated(ProcurementRequest $procurement): void
    {
        try {
            $user = $procurement->user;
            if (!$user || empty($user->phone_number)) {
                return;
            }

            $wa = new WhatsAppService();
            $prNo = $procurement->final_number ?? $procurement->no;

            $pesan = "Halo *{$user->name}*,\n\n";
            $pesan .= "PR Final untuk pengajuan Anda telah dibuat oleh Admin.\n\n";
            $pesan .= "No PR Final: `{$prNo}`\n";
            $pesan .= "Status: *Menunggu Review Format*\n\n";
            $pesan .= "Silakan pantau terus sistem untuk melihat perkembangan statusnya.";

            $wa->send($user->phone_number, $pesan);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp notify user (Final PR) failed for PR #' . $procurement->id . ': ' . $e->getMessage());
        }
    }

    public function updateFinalNumber(Request $request, ProcurementRequest $procurement)
    {
        // Pastikan hanya Admin yang bisa mengubah
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Admin'])) {
            abort(403, 'Only Admin can edit the Final PR Number.');
        }

        // Gabungkan prefix dan suffix
        $fullNumber = $request->input('no_prefix') . '/PR/' . $request->input('no_suffix');

        $request->validate([
            'no_prefix' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($fullNumber, $procurement) {
                    $existsInNo = \App\Models\ProcurementRequest::where('no', $fullNumber)
                        ->where('id', '!=', $procurement->id)
                        ->exists();
                    $existsInFinal = \App\Models\ProcurementRequest::where('final_number', $fullNumber)
                        ->where('id', '!=', $procurement->id)
                        ->exists();
                    
                    if ($existsInNo || $existsInFinal) {
                        $fail('Nomor PR ini sudah digunakan.');
                    }
                },
            ],
        ], [
            'no_prefix.required' => 'Nomor urut PR wajib diisi.'
        ]);

        // HANYA update kolom final_number
        $procurement->update(['final_number' => $fullNumber]);

        return back()->with('success', 'Nomor Final PR berhasil diperbarui menjadi ' . $fullNumber);
    }

    public function approveFinalFormat(ProcurementRequest $procurementRequest)
    {
        // Pastikan nomor sudah diisi sebelum approve
        if (!$procurementRequest->final_number) {
            return back()->with('error', 'Nomor Final PR belum diisi. Silakan input nomor terlebih dahulu.');
        }

        if ($procurementRequest->status === 'pending_review_final') {
            $procurementRequest->update([
                'status' => 'pending_final_sign',
            ]);

            // ═══ TRIGGER WA KE USER (Format PR Final disetujui) ═══
            $this->notifyUserFormatApproved($procurementRequest);
            // ══════════════════════════════════════════════════════

            return back()->with('success', 'Format PR Final disetujui. Silakan cetak dan minta TTD Manajer.');
        }

        return back()->with('error', 'Gagal Approve. Status saat ini: "' . $procurementRequest->status . '".');
    }

    /**
     * ═══ TRIGGER WA: Notifikasi ke User saat Format PR Final disetujui ═══
     */
    private function notifyUserFormatApproved(ProcurementRequest $procurement): void
    {
        try {
            $user = $procurement->user;
            if (!$user || empty($user->phone_number)) {
                return;
            }

            $wa = new WhatsAppService();
            $prNo = $procurement->final_number ?? $procurement->no;

            $pesan = "Halo *{$user->name}*,\n\n";
            $pesan .= "Kabar baik! Format *PR Final* Anda telah disetujui.\n\n";
            $pesan .= "No PR Final: `{$prNo}`\n";
            $pesan .= "Status: *Menunggu TTD Manajer*\n\n";
            $pesan .= "Dokumen fisik sedang dalam proses tanda tangan. Kami akan mengabari lagi jika sudah selesai.";

            $wa->send($user->phone_number, $pesan);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp notify user (Format Approved) failed for PR #' . $procurement->id . ': ' . $e->getMessage());
        }
    }

    public function validateFinalSignature(ProcurementRequest $procurementRequest)
    {
        if ($procurementRequest->status !== 'pending_final_sign') {
            return back()->with('error', 'Status tidak valid untuk validasi TTD Final.');
        }

        $procurementRequest->update([
            'status' => 'final_approved',
        ]);

        // ═══ TRIGGER WA KE USER (TTD Final Selesai) ═══
        $this->notifyUserFinalApproved($procurementRequest);
        // ══════════════════════════════════════════════

        return back()->with('success', 'TTD Final berhasil divalidasi. Anda sekarang bisa membuat PO.');
    }

    /**
     * ═══ TRIGGER WA: Notifikasi ke User saat TTD Final divalidasi ═══
     */
    private function notifyUserFinalApproved(ProcurementRequest $procurement): void
    {
        try {
            $user = $procurement->user;
            if (!$user || empty($user->phone_number)) {
                return;
            }

            $wa = new WhatsAppService();
            $prNo = $procurement->final_number ?? $procurement->no;
            $total = number_format((float) $procurement->final_total_price, 0, ',', '.');

            $pesan = "Halo *{$user->name}*,\n\n";
            $pesan .= "PR Final Anda telah *SELESAI divalidasi* (TTD lengkap).\n\n";
            $pesan .= "No PR Final: `{$prNo}`\n";
            $pesan .= "Total Nilai: *Rp {$total}*\n";
            $pesan .= "Vendor Pemenang: " . ($procurement->winnerVendor->vendor_name ?? '-') . "\n\n";
            $pesan .= "Silakan ambil dokumen fisiknya di Admin untuk diproses ke tahap PO.";

            $wa->send($user->phone_number, $pesan);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp notify user (Final Approved) failed for PR #' . $procurement->id . ': ' . $e->getMessage());
        }
    }

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
                ->route('procurement.comparisons.index', ['procurement' => $procurement->id])
                ->with('success', $message);

        } catch (Throwable $e) {
            Log::error('AI Scan Error: ' . $e->getMessage(), [
                'procurement_id' => $procurement->id,
                'mode'           => $mode,
            ]);

            return back()->with('error', 'Failed to scan screenshot: ' . $e->getMessage());
        }
    }

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
            $subtotal    = $v['subtotal'] ?? 0;
            $shipping    = $v['shipping_cost'] ?? 0;
            $discount    = $v['discount_voucher'] ?? 0;
            $tax         = $v['tax_ppn'] ?? 0;

            $totalBeforeTax = $subtotal + $shipping - $discount;
            $totalPrice     = $totalBeforeTax + $tax;

            $vendor = $procurement->vendors()->create([
                'vendor_name'        => $v['vendor_name'],
                'marketplace'        => $v['marketplace'] ?? null,
                'company_name'       => $v['company_name'] ?? null,
                'payment_method'     => $v['payment_method'] ?? null,
                'payment_bank'       => $v['payment_bank'] ?? null,
                'estimated_delivery' => $v['estimated_delivery'] ?? null,
                'product_url'        => $v['product_url'] ?? null,
                'notes'              => $v['notes'] ?? null,
                'subtotal'           => $subtotal,
                'shipping_cost'      => $shipping,
                'discount_voucher'   => $discount,
                'tax_ppn'            => $tax,
                'total_before_tax'   => $totalBeforeTax,
                'total_price'        => $totalPrice,
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
            ->route('procurement.comparisons.index', ['procurement' => $procurement->id])
            ->with('success', 'Vendor data saved successfully.');
    }

    public function selectVendor(ProcurementRequest $procurement, ProcurementVendor $vendor)
    {
        $user = auth()->user();

        // ═══ LOGIKA FILTER DATA SESUAI ROLE ═══
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurement->department_id !== $user->department_id) {
                    abort(403, 'You do not have access to this PR.');
                }
            } else {
                if ($procurement->user_id !== $user->id) {
                    abort(403, 'You do not have access to this PR.');
                }
            }
        }
        // ═════════════════════════════════════════

        if ($vendor->procurement_request_id !== $procurement->id) {
            abort(404);
        }

        if ($procurement->status !== 'approved') {
            return back()->with('error', 'Winner can only be changed while status is Approved.');
        }

        $procurement->vendors()->update(['is_winner' => false]);
        $vendor->update(['is_winner' => true]);

        $procurement->update([
            'final_total_price' => $vendor->total_price,
            'status'            => 'processed',
        ]);

        return back()->with('success', 'Winning vendor selected: ' . $vendor->vendor_name . '. PR is now in Tahap 2.');
    }

    public function destroy(ProcurementVendor $vendor)
    {
        $user = auth()->user();
        $procurement = $vendor->procurementRequest;

        // ═══ LOGIKA FILTER DATA SESUAI ROLE ═══
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurement->department_id !== $user->department_id) {
                    abort(403, 'You do not have access to this PR.');
                }
            } else {
                if ($procurement->user_id !== $user->id) {
                    abort(403, 'You do not have access to this PR.');
                }
            }
        }
        // ═════════════════════════════════════════

        $vendor->delete();

        $this->autoPickWinner($procurement);

        return back()->with('success', 'Vendor deleted successfully.');
    }

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

    private function getRomawi($number)
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $map[$number] ?? '';
    }
}