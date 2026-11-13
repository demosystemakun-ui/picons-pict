@extends('layouts.app')

@section('title', 'Vendor Comparison')
@section('page-title', 'Vendor Price Comparison')

@php
    $hasVendors = $procurement->vendors->count() > 0;
    
    // ═══ AMBIL STATUS DARI MASING-MASING TABEL ═══
    $statusPR1 = $procurement->status; // PR 1 (pending, approved, processed, dll)
    $statusPR2 = $procurement->finalComparison?->status; // PR 2 (pending_review_final, dll)
    
    // Cek status untuk alur
    $isPendingFinalReview = $statusPR2 === 'pending_review_final';
    $isPendingFinalSign   = $statusPR2 === 'pending_final_sign';
    $isFinalApproved      = in_array($statusPR2, ['final_approved', 'received', 'completed']);
    $isProcessed          = $statusPR1 === 'processed'; // Sudah pilih vendor, belum buat PR Final
    
    // Form input hanya muncul jika status PR 1 'approved' (belum pilih vendor)
    $canShowInputForms = $statusPR1 === 'approved';
    
    // Cek role admin
    $isAdmin = auth()->user()->hasAnyRole(['Super Admin', 'Admin']);
@endphp

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12" x-data="{ open: {{ $hasVendors ? 'false' : 'true' }} }">

    {{-- ═══ HEADER ═══ --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Vendor Comparison</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                PR Awal: <span class="font-mono font-medium text-gray-700 dark:text-gray-300">{{ $procurement->no }}</span>
                @if($procurement->finalComparison?->final_number)
                    | PR Final: <span class="font-mono font-medium text-indigo-600 dark:text-indigo-400">{{ $procurement->finalComparison->final_number }}</span>
                @endif
                — {{ $procurement->description }}
            </p>
        </div>
        <a href="{{ route('procurement.show', $procurement) }}"
           class="inline-flex items-center gap-1.5 self-start rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Detail
        </a>
    </div>

    {{-- ═══ ALERTS ═══ --}}
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-5 py-4 dark:bg-emerald-900/20 dark:border-emerald-800/60">
            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-5 py-4 dark:bg-rose-900/20 dark:border-rose-800/60">
            <p class="text-sm font-medium text-rose-800 dark:text-rose-300">{{ session('error') }}</p>
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-5 py-4 dark:bg-rose-900/20 dark:border-rose-800/60">
            <ul class="text-sm text-rose-700 dark:text-rose-400 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ═══ AI SCAN SECTION ═══ --}}
    @if($canShowInputForms)
    <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-teal-50 to-emerald-50/50 dark:from-teal-900/20 dark:to-emerald-900/10 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-100 dark:bg-teal-900/50 text-teal-600 dark:text-teal-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Scan Vendor Screenshots with AI</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Select files, then click the scan button</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white dark:bg-gray-800 border border-teal-200 dark:border-teal-800 px-3 py-1 text-xs font-semibold text-teal-700 dark:text-teal-400">
                AI Gemini Vision
            </span>
        </header>

        <div class="p-6 space-y-5">
            <label for="multiScreenshotInput"
                   class="group relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-teal-300 dark:border-teal-700 bg-teal-50/30 dark:bg-teal-900/10 px-6 py-8 text-center transition cursor-pointer hover:border-teal-400 dark:hover:border-teal-600 hover:bg-teal-50/60 dark:hover:bg-teal-900/20">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 dark:bg-teal-900/40 text-teal-600 dark:text-teal-400 group-hover:scale-105 transition-transform">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p id="fileCountInfo" class="text-sm font-semibold text-gray-900 dark:text-white">Click to select screenshots</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Shopee, Tokopedia, Bukalapak, Lazada — multiple files allowed</p>
                </div>
                <input type="file" id="multiScreenshotInput" accept="image/*" multiple class="sr-only">
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <form action="{{ route('procurement.comparisons.scan.monotaro', $procurement) }}" method="POST" enctype="multipart/form-data" onsubmit="return transferFiles(this, 'scanMonotaroBtn')">
                    @csrf
                    <input type="file" name="screenshots[]" multiple class="hidden-file-input" data-target="monotaro">
                    <button type="submit" id="scanMonotaroBtn" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:from-blue-700 hover:to-indigo-700 transition">
                        Scan Monotaro
                    </button>
                </form>

                <form action="{{ route('procurement.comparisons.scan.others', $procurement) }}" method="POST" enctype="multipart/form-data" onsubmit="return transferFiles(this, 'scanOthersBtn')">
                    @csrf
                    <input type="file" name="screenshots[]" multiple class="hidden-file-input" data-target="others">
                    <button type="submit" id="scanOthersBtn" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-orange-500 to-rose-500 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:from-orange-600 hover:to-rose-600 transition">
                        Scan Other Marketplaces
                    </button>
                </form>
            </div>
        </div>
    </section>
    @endif

    {{-- ═══ TABLE VENDOR ═══ --}}
    <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Comparison Recap</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $procurement->vendors->count() }} vendors registered</p>
            </div>
        </header>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/50">
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Vendor / Store</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 text-right">Subtotal</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 text-right">Shipping</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 text-right">Discount</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 text-right">Tax</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 text-right">Total</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 text-center">Status</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($procurement->vendors as $vendor)
                        @php $isSelected = (bool) $vendor->is_winner; @endphp
                        <tr class="{{ $isSelected ? 'bg-teal-50/50 dark:bg-teal-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                            <td class="px-5 py-4 font-semibold text-gray-900 dark:text-white">{{ $vendor->vendor_name }}</td>
                            <td class="px-5 py-4 text-right text-gray-600 dark:text-gray-300">Rp {{ number_format($vendor->subtotal, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right text-gray-600 dark:text-gray-300">Rp {{ number_format($vendor->shipping_cost, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right text-rose-600 dark:text-rose-400">- Rp {{ number_format($vendor->discount_voucher, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right text-gray-600 dark:text-gray-300">Rp {{ number_format($vendor->tax_ppn, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right font-bold text-teal-600 dark:text-teal-400">Rp {{ number_format($vendor->total_price, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-center">
                                @if($isSelected)
                                    <span class="rounded-full bg-emerald-100 dark:bg-emerald-900/40 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400">Selected</span>
                                @elseif($statusPR1 === 'approved')
                                    <form action="{{ route('procurement.comparisons.select', [$procurement, $vendor]) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="rounded-md px-2.5 py-1 text-xs font-semibold text-teal-600 hover:bg-teal-50 dark:text-teal-400 dark:hover:bg-teal-900/30">Select</button>
                                    </form>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if($statusPR1 === 'approved')
                                    <form action="{{ route('procurement.comparisons.destroy', [$procurement, $vendor]) }}" method="POST" onsubmit="return confirm('Delete vendor?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-rose-600 dark:text-rose-400 hover:underline">Delete</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">Locked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">No comparison data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ═══ FOOTER: Aksi Berdasarkan Status ═══ --}}
        @if($hasVendors && $procurement->winnerVendor)
            <footer class="px-6 py-5 border-t border-gray-100 dark:border-gray-700 bg-gradient-to-r from-teal-50/50 to-emerald-50/50 dark:from-teal-900/10 dark:to-emerald-900/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                        @if($isFinalApproved)
                            Final PR Validated
                        @elseif($isPendingFinalSign)
                            Waiting for Final Signature
                        @elseif($isPendingFinalReview)
                            Waiting for Format Review
                        @else
                            Vendor Selected — Ready for Final PR
                        @endif
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        @if($isFinalApproved)
                            You can now proceed to the PO/Cash Advance/Reimbursement process.
                        @elseif($isPendingFinalSign)
                            Please print the PDF, get the Manager's wet signature, then click Validate.
                        @elseif($isPendingFinalReview)
                            Please review the Final PR format, then click Approve Format.
                        @else
                            Please continue to create the Final PR Form.
                        @endif
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-2">
                    
                    {{-- ═══ JIKA STATUS MENUNGGU REVIEW FINAL (PENDING_REVIEW_FINAL) ═══ --}}
                    @if($isPendingFinalReview)
                        
                        {{-- KONDISI A: JIKA NOMOR BELUM DIISI (Hanya untuk Admin) --}}
                        @if($isAdmin && empty($procurement->finalComparison?->final_number))
                            <form action="{{ route('procurement.comparisons.update-final-number', $procurement) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <div class="flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 overflow-hidden focus-within:ring-2 focus-within:ring-teal-500">
                                    <input type="text" name="no_prefix" required placeholder="15"
                                           class="w-16 text-center border-none text-sm px-2 py-2 font-mono focus:ring-0 focus:outline-none">
                                    <span class="text-sm font-mono text-gray-500 bg-gray-50 dark:bg-gray-800 px-3 py-2 border-l border-gray-200 dark:border-gray-700">
                                        /PR/{{ preg_replace('/^[0-9]+\/PR\//', '', $procurement->no) }}
                                    </span>
                                </div>
                                <input type="hidden" name="no_suffix" value="{{ preg_replace('/^[0-9]+\/PR\//', '', $procurement->no) }}">
                                <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700 shadow-sm whitespace-nowrap">
                                    Simpan Nomor
                                </button>
                            </form>

                        {{-- KONDISI B: JIKA NOMOR SUDAH DIISI (Tampilkan Tombol Print Draft & Approve) --}}
                        @elseif($isAdmin && !empty($procurement->finalComparison?->final_number))
                            
                            {{-- TOMBOL PRINT & PDF DRAFT --}}
                            <a href="{{ route('procurement.print.final', $procurement) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                Print Draft
                            </a>
                            <a href="{{ route('procurement.pdf.final', $procurement) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-teal-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-teal-700 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/30">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                PDF Draft
                            </a>
                            {{-- END TOMBOL PRINT & PDF DRAFT --}}

                            <form action="{{ route('procurement.approve-final-format', $procurement) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm" onclick="return confirm('Are you sure the Final PR format is correct?')">
                                    <svg class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Approve Format
                                </button>
                            </form>

                        {{-- KONDISI C: TAMPILAN UNTUK USER BIASA --}}
                        @elseif(!$isAdmin)
                            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-3 py-2 rounded-lg text-xs font-medium border border-amber-200 dark:border-amber-800/50">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Waiting for Admin to input number & review format.
                            </div>
                        @endif

                    {{-- ═══ JIKA FORMAT SUDAH DI-APPROVE (MUNCUL PRINT PDF & VALIDASI TTD) ═══ --}}
                    @elseif($isPendingFinalSign)
                        <a href="{{ route('procurement.pdf.final', $procurement) }}" target="_blank" class="rounded-lg border border-teal-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-teal-700 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/30">
                            Print PR Final
                        </a>

                        @if($isAdmin)
                            <form action="{{ route('procurement.validate-final-signature', $procurement) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 shadow-sm" onclick="return confirm('Is the Manager signature affixed and are you sure you want to validate?')">
                                    Validate Final Signature
                                </button>
                            </form>
                        @endif

                    {{-- ═══ JIKA SUDAH FINAL APPROVED (MUNCUL TOMBOL PO, DLL) ═══ --}}
                    @elseif($isFinalApproved)
                        
                        {{-- Tombol Create PO/Cash Advance/Reimbursement HANYA untuk Admin, Super Admin, Admin Departemen --}}
                        @if($isAdmin)
                            @if($procurement->procurement_method === 'po')
                                <a href="{{ route('procurement.purchases.create', $procurement) }}" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm">
                                    Create PO
                                </a>
                            @elseif($procurement->procurement_method === 'cash_advance')
                                <a href="{{ route('cash-advances.create', ['procurement_request_id' => $procurement->id]) }}" class="rounded-lg bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700 shadow-sm">
                                    Create Cash Advance
                                </a>
                            @elseif($procurement->procurement_method === 'reimbursement')
                                <a href="{{ route('reimbursement.create', ['procurement_request_id' => $procurement->id]) }}" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 shadow-sm">
                                    Create Reimbursement
                                </a>
                            @endif
                        @else
                            {{-- Tampilan untuk user biasa (hanya bisa lihat) --}}
                            <div class="flex items-center gap-2 text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-900/20 px-3 py-2 rounded-lg text-xs font-medium border border-teal-200 dark:border-teal-800/50">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Final PR Validated. Waiting for Admin to create PO.
                            </div>
                        @endif

                        <a href="{{ route('procurement.pdf.final', $procurement) }}" target="_blank" class="rounded-lg border border-teal-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-teal-700 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/30">
                            View Final PR PDF
                        </a>

                    {{-- ═══ JIKA SUDAH PILIH VENDOR (PROCESSED / APPROVED) ═══ --}}
                    @else
                        <a href="{{ route('procurement.comparisons.final-pr.create', $procurement) }}"
                           class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-indigo-700 hover:to-purple-700 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Save & Continue to Final PR
                        </a>
                    @endif
                </div>
            </footer>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
    let sharedFileBuffer = new DataTransfer();

    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('multiScreenshotInput');
        if (!input) return;

        input.addEventListener('change', function () {
            for (let i = 0; i < input.files.length; i++) {
                sharedFileBuffer.items.add(input.files[i]);
            }
            input.files = sharedFileBuffer.files;

            const count = input.files.length;
            const label = document.getElementById('fileCountInfo');
            if (label) {
                if (count > 0) {
                    label.innerText = `${count} file(s) collected (ready to process)`;
                    label.classList.add('text-teal-600');
                } else {
                    label.innerText = 'Click to select screenshots';
                }
            }
        });
    });

    function transferFiles(form, buttonId) {
        if (sharedFileBuffer.files.length === 0) {
            alert('Please select screenshots first before scanning.');
            return false;
        }
        const hiddenInput = form.querySelector('.hidden-file-input');
        if (hiddenInput) {
            hiddenInput.files = sharedFileBuffer.files;
        }
        const btn = document.getElementById(buttonId);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `Processing...`;
        }
        return true;
    }

    let vendorIndex = 1;
    function addVendorRow() {
        const container = document.getElementById('vendorRowsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'vendor-row grid grid-cols-1 md:grid-cols-12 gap-3 items-end p-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/30 mt-3';
        
        newRow.innerHTML = `
            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Vendor Name <span class="text-red-500">*</span></label>
                <input type="text" name="vendors[${vendorIndex}][vendor_name]" required placeholder="e.g. Toko Jaya" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Marketplace</label>
                <input type="text" name="vendors[${vendorIndex}][marketplace]" placeholder="Shopee" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Subtotal (Rp)</label>
                <input type="number" name="vendors[${vendorIndex}][subtotal]" value="0" min="0" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Ongkir (Rp)</label>
                <input type="number" name="vendors[${vendorIndex}][shipping_cost]" value="0" min="0" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
            </div>
            <div class="md:col-span-2 flex items-center gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Diskon (Rp)</label>
                    <input type="number" name="vendors[${vendorIndex}][discount_voucher]" value="0" min="0" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                </div>
                <button type="button" onclick="this.closest('.vendor-row').remove()" class="mt-5 p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-md transition" title="Hapus Baris">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
        `;
        container.appendChild(newRow);
        vendorIndex++;
    }
</script>
@endpush