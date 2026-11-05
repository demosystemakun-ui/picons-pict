<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\ProcurementVendor;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VendorComparisonController extends Controller
{
    /* ═══════════════════════════════════════════════════════════════
       INDEX — Halaman Perbandingan Vendor Utama
    ═══════════════════════════════════════════════════════════════ */
    public function index(ProcurementRequest $procurement)
    {
        $user = auth()->user();
        
        // 🌟 Pembatasan Akses: Admin Departemen hanya bisa akses PR departemennya
        if ($user->hasRole('Admin Departemen') && $procurement->department_id !== $user->department_id) {
            abort(403, 'Anda tidak memiliki akses ke perbandingan vendor departemen lain.');
        }

        $procurement->load('vendors.items', 'winnerVendor');
        $vendors = $procurement->vendors()->get();

        return view('procurement.comparison.index', compact('procurement', 'vendors'));
    }

    /* ═══════════════════════════════════════════════════════════════
       STORE — Simpan Data Vendor (Manual / Setelah AI Scan)
    ═══════════════════════════════════════════════════════════════ */
    public function store(Request $request, ProcurementRequest $procurement)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin Departemen') && $procurement->department_id !== $user->department_id) {
            abort(403, 'Anda tidak memiliki akses untuk menyimpan vendor di departemen lain.');
        }

        $validated = $request->validate([
            'vendors'                => 'required|array|min:1',
            'vendors.*.vendor_name'  => 'required|string|max:255',
            'vendors.*.marketplace'  => 'nullable|string|max:255',
            'vendors.*.company_name' => 'nullable|string|max:255',
            'vendors.*.total_price'  => 'required|numeric|min:0',
            'vendors.*.tax_note'     => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Hapus vendor lama jika admin re-input
            foreach ($procurement->vendors as $oldVendor) {
                foreach ($oldVendor->items as $oldItem) {
                    if ($oldItem->picture && Storage::disk('supabase-procurement_request')->exists($oldItem->picture)) {
                        Storage::disk('supabase-procurement_request')->delete($oldItem->picture);
                    }
                }
            }
            $procurement->vendors()->delete();

            foreach ($validated['vendors'] as $index => $vendor) {
                $procurement->vendors()->create([
                    'vendor_name'  => $vendor['vendor_name'],
                    'marketplace'  => $vendor['marketplace'] ?? null,
                    'company_name' => $vendor['company_name'] ?? null,
                    'total_price'  => $vendor['total_price'],
                    'tax_note'     => $vendor['tax_note'] ?? null,
                    'sort_order'   => $index + 1,
                    'is_winner'    => false,
                ]);
            }

            DB::commit();
            return redirect()
                ->route('procurement.comparisons.index', $procurement)
                ->with('success', 'Data perbandingan vendor berhasil disimpan.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan vendor: ' . $e->getMessage())->withInput();
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       SELECT VENDOR — Pilih Vendor Pemenang
    ═══════════════════════════════════════════════════════════════ */
    public function selectVendor(Request $request, ProcurementRequest $procurement, ProcurementVendor $comparison)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin Departemen') && $procurement->department_id !== $user->department_id) {
            abort(403, 'Anda tidak memiliki akses ke vendor departemen lain.');
        }

        // Validasi: pastikan vendor memang milik PR ini
        if ($comparison->procurement_request_id !== $procurement->id) {
            abort(404, 'Vendor tidak ditemukan pada PR ini.');
        }

        $validated = $request->validate([
            'reason_choose_vendor'  => 'required|string|max:2000',
            'budget_type'           => 'nullable|in:budgeted,non_budgeted',
            'accounting_mgr_name'   => 'nullable|string|max:255',
            'ceo_name'              => 'nullable|string|max:255',
            'cfo_name'              => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Reset semua vendor → bukan pemenang
            $procurement->vendors()->update(['is_winner' => false]);

            // Set 1 vendor sebagai pemenang
            $comparison->update(['is_winner' => true]);

            // Update PR dengan data vendor pemenang
            $procurement->update([
                'final_total_price'    => $comparison->total_price,
                'reason_choose_vendor' => $validated['reason_choose_vendor'],
                'budget_type'          => $validated['budget_type'] ?? $procurement->budget_type,
                'accounting_mgr_name'  => $validated['accounting_mgr_name'] ?? $procurement->accounting_mgr_name,
                'ceo_name'             => $validated['ceo_name'] ?? $procurement->ceo_name,
                'cfo_name'             => $validated['cfo_name'] ?? $procurement->cfo_name,
            ]);

            DB::commit();

            return redirect()
                ->route('procurement.comparisons.index', $procurement)
                ->with('success', 'Vendor pemenang berhasil dipilih. Silakan cetak PR Final dan minta TTD Manajer.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memilih vendor: ' . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       DESTROY — Hapus Data Vendor
    ═══════════════════════════════════════════════════════════════ */
    public function destroy(ProcurementVendor $comparison)
    {
        $user = auth()->user();
        $procurement = $comparison->procurementRequest;
        
        if ($user->hasRole('Admin Departemen') && $procurement->department_id !== $user->department_id) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus vendor departemen lain.');
        }

        // Jangan izinkan hapus jika status PR sudah Processed
        if (in_array($procurement->status, ['processed', 'received', 'completed'])) {
            return back()->with('error', 'Vendor tidak dapat dihapus karena PR sudah diproses.');
        }

        // Hapus file gambar item vendor
        foreach ($comparison->items as $item) {
            if ($item->picture && Storage::disk('supabase-procurement_request')->exists($item->picture)) {
                Storage::disk('supabase-procurement_request')->delete($item->picture);
            }
        }

        $comparison->items()->delete();
        $comparison->delete();

        return back()->with('success', 'Data vendor berhasil dihapus.');
    }
}