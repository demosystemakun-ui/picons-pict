@extends('layouts.app')

@section('title', 'Vendor Comparison')
@section('page-title', 'Vendor Price Comparison (E-Commerce)')

@php
    $hasSelectedVendor = isset($procurement) && $procurement->vendors->where('is_winner', true)->count() > 0;
    $isApproved      = isset($procurement) && $procurement->status === 'approved';
    $isProcessed     = isset($procurement) && $procurement->status === 'processed';
    $isFinalApproved = isset($procurement) && $procurement->status === 'final_approved';
@endphp

@section('content')
<div class="space-y-6 pb-12">

    {{-- ═══════════════════════════════════════════════════════════════
         PAGE HEADER
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Vendor Comparison
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Compare prices and specifications from multiple e-commerce vendors.
            </p>
        </div>

        <a href="{{ route('procurement.show', $procurement) }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to PR Detail
        </a>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         PROCUREMENT DOCUMENT INFO
    ═══════════════════════════════════════════════════════════════ --}}
    @isset($procurement)
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-teal-50 to-white dark:from-teal-950/30 dark:to-gray-800 p-6 shadow-sm">
            <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-100 dark:bg-teal-900/50 text-teal-600 dark:text-teal-400 shrink-0">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-teal-600 dark:text-teal-400">
                            Initial PR (Internal)
                        </div>
                        <div class="mt-0.5 font-mono text-sm font-bold text-gray-900 dark:text-white">
                            {{ $procurement->no }}
                        </div>

                        @if($procurement->final_number)
                            <div class="mt-2 text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                                Final PR (Vendor)
                            </div>
                            <div class="font-mono text-sm font-bold text-indigo-700 dark:text-indigo-300">
                                {{ $procurement->final_number }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 self-start sm:self-center">
                    @if($hasSelectedVendor)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300 ring-1 ring-inset ring-emerald-200 dark:ring-emerald-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Vendor Selected
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 dark:bg-amber-900/40 px-3 py-1 text-xs font-semibold text-amber-700 dark:text-amber-300 ring-1 ring-inset ring-amber-200 dark:ring-amber-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Not Selected
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endisset

    {{-- ═══════════════════════════════════════════════════════════════
         ALERTS
    ═══════════════════════════════════════════════════════════════ --}}
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/60 px-5 py-4">
            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800/60 px-5 py-4">
            <p class="text-sm font-medium text-rose-800 dark:text-rose-300">{{ session('error') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800/60 px-5 py-4">
            <ul class="text-sm text-rose-700 dark:text-rose-300 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         AI SCAN (Only if status = approved)
    ═══════════════════════════════════════════════════════════════ --}}
    @if($isApproved)
    <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-teal-50 to-emerald-50/50 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Scan Vendor Screenshots</h2>
                <p class="text-xs text-gray-500">Upload Monotaro or other marketplaces (Shopee, Tokopedia, etc.)</p>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-teal-200 px-3 py-1 text-xs font-semibold text-teal-700">
                AI Gemini Vision
            </span>
        </header>

        <div class="p-6 space-y-5">
            <label for="multiScreenshotInput" class="group relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-teal-300 bg-teal-50/30 px-6 py-8 text-center cursor-pointer hover:border-teal-400 hover:bg-teal-50/60">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 text-teal-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p id="fileCountInfo" class="text-sm font-semibold text-gray-900">Click to select screenshots</p>
                    <p class="mt-1 text-xs text-gray-500">Select files first, then click the scan button</p>
                </div>
                <input type="file" id="multiScreenshotInput" accept="image/*" multiple class="sr-only">
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <form action="{{ route('procurement.comparisons.scan.monotaro', $procurement) }}" method="POST" enctype="multipart/form-data" onsubmit="return transferFiles(this, 'scanMonotaroBtn')">
                    @csrf
                    <input type="file" name="screenshots[]" multiple class="hidden-file-input">
                    <button type="submit" id="scanMonotaroBtn" class="w-full rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:from-blue-700 hover:to-indigo-700 transition">
                        Scan Monotaro
                    </button>
                </form>

                <form action="{{ route('procurement.comparisons.scan.others', $procurement) }}" method="POST" enctype="multipart/form-data" onsubmit="return transferFiles(this, 'scanOthersBtn')">
                    @csrf
                    <input type="file" name="screenshots[]" multiple class="hidden-file-input">
                    <button type="submit" id="scanOthersBtn" class="w-full rounded-lg bg-gradient-to-r from-orange-500 to-rose-500 px-5 py-3 text-sm font-semibold text-white hover:from-orange-600 hover:to-rose-600 transition">
                        Scan Other Marketplaces
                    </button>
                </form>
            </div>

            <p class="text-xs text-gray-400 text-center">
                <strong class="text-blue-600">Monotaro</strong>: specifically for monotaro.id &nbsp;·&nbsp;
                <strong class="text-orange-600">Other</strong>: Shopee, Tokopedia, Lazada, etc.
            </p>
        </div>
    </section>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         VENDOR TABLE (With Product Details)
    ═══════════════════════════════════════════════════════════════ --}}
    <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Comparison Recap</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $procurement->vendors->count() }} vendors registered</p>
            </div>
        </header>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/40">
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500">Vendor</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">Subtotal</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">Shipping</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">Discount</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">VAT</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">Total</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-center">Status</th>
                        @if($isApproved)
                            <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70">
                    @forelse($procurement->vendors as $vendor)
                        @php $isWinner = (bool) $vendor->is_winner; @endphp
                        
                        {{-- Vendor Main Row --}}
                        <tr class="{{ $isWinner ? 'bg-teal-50/50 dark:bg-teal-950/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/30' }}">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $vendor->vendor_name }}</div>
                                <div class="text-xs text-gray-500">{{ $vendor->marketplace }}</div>
                                @if($isWinner)
                                    <div class="text-xs font-medium text-teal-600 dark:text-teal-400 mt-0.5">★ Selected (Cheapest)</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">Rp {{ number_format($vendor->subtotal, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right">Rp {{ number_format($vendor->shipping_cost, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right text-rose-600">- Rp {{ number_format($vendor->discount_voucher, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right">Rp {{ number_format($vendor->tax_ppn, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right font-bold text-teal-600">Rp {{ number_format($vendor->total_price, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-center">
                                @if($isWinner)
                                    <span class="rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400">Selected</span>
                                @elseif($isApproved)
                                    <form action="{{ route('procurement.comparisons.select', [$procurement, $vendor]) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="rounded-md px-2.5 py-1 text-xs font-semibold text-teal-600 hover:bg-teal-50">Select</button>
                                    </form>
                                @else
                                    —
                                @endif
                            </td>
                            @if($isApproved)
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- 🌟 VIEW DETAILS BUTTON --}}
                                        @if($vendor->items->count() > 0)
                                            <button type="button" onclick="toggleDetails('details-{{ $vendor->id }}')" class="rounded-md px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:bg-indigo-50">
                                                View Details ({{ $vendor->items->count() }})
                                            </button>
                                        @endif

                                        {{-- Delete Button --}}
                                        <form action="{{ route('procurement.comparisons.destroy', $vendor) }}" method="POST" onsubmit="return confirm('Delete vendor?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:underline text-xs">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>

                        {{-- 🌟 PRODUCT DETAILS ROW (Hidden by default) --}}
                        <tr id="details-{{ $vendor->id }}" class="hidden bg-gray-50/50 dark:bg-gray-900/30">
                            <td colspan="{{ $isApproved ? 8 : 7 }}" class="px-5 py-4">
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                                    <table class="w-full text-xs">
                                        <thead class="bg-gray-100 dark:bg-gray-900/50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-gray-600 dark:text-gray-400">Product Name</th>
                                                <th class="px-3 py-2 text-left text-gray-600 dark:text-gray-400">Variant</th>
                                                <th class="px-3 py-2 text-center text-gray-600 dark:text-gray-400">Qty</th>
                                                <th class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">Unit Price</th>
                                                <th class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                            @foreach($vendor->items as $item)
                                                <tr>
                                                    <td class="px-3 py-2 text-gray-900 dark:text-white">{{ $item->product_name }}</td>
                                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $item->variant ?? '-' }}</td>
                                                    <td class="px-3 py-2 text-center text-gray-900 dark:text-white">{{ $item->quantity }}</td>
                                                    <td class="px-3 py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                    <td class="px-3 py-2 text-right font-semibold text-teal-600">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-16 text-center text-gray-500">No comparison data yet. Upload screenshots above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════
             FOOTER: Step-by-Step Validation
        ═══════════════════════════════════════════════════════════════ --}}
        @if($hasSelectedVendor)
            <footer class="px-6 py-5 border-t border-gray-100 dark:border-gray-700 bg-gradient-to-r from-teal-50/50 to-emerald-50/50 dark:from-teal-950/20 dark:to-emerald-950/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                        @if($isFinalApproved)
                            Final PR Validated
                        @elseif($isProcessed)
                            Initial PR Validated — Ready for Final PR
                        @else
                            Vendor Selected — Waiting for Initial PR Validation
                        @endif
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                        @if($isFinalApproved)
                            You can now proceed to the PO / Cash Advance / Reimbursement process.
                        @elseif($isProcessed)
                            Print the Final PR, get Manager signature, then input Final PR Number to validate.
                        @else
                            Print the Initial PR, get Manager signature, then validate below.
                        @endif
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-2">
                    @if($isFinalApproved)
                        @if($procurement->procurement_method === 'po')
                            <a href="{{ route('procurement.purchases.create', $procurement) }}" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Create PO</a>
                        @elseif($procurement->procurement_method === 'cash_advance')
                            <a href="{{ route('cash-advances.create', ['procurement_request_id' => $procurement->id]) }}" class="rounded-lg bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">Create Cash Advance</a>
                        @elseif($procurement->procurement_method === 'reimbursement')
                            <a href="{{ route('reimbursement.create', ['procurement_request_id' => $procurement->id]) }}" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Create Reimbursement</a>
                        @endif
                    @elseif($isProcessed)
                        <a href="{{ route('procurement.pdf.final', $procurement) }}" target="_blank" class="rounded-lg border border-teal-600 bg-white px-4 py-2 text-sm font-semibold text-teal-700 hover:bg-teal-50">
                            Print Final PR
                        </a>

                        <form action="{{ route('procurement.validate-final-signature', $procurement) }}" method="POST" class="flex items-center gap-2">
                            @csrf @method('PATCH')
                            <input type="text" name="final_number" required placeholder="Final PR Number (e.g. 07/OPS-PICT/CAF/IV/2026)" class="rounded-lg border-gray-300 text-sm px-3 py-2 w-64">
                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700" onclick="return confirm('Manager signature confirmed?')">
                                Validate Final PR
                            </button>
                        </form>
                    @else
                        <a href="{{ route('procurement.pdf', $procurement) }}" target="_blank" class="rounded-lg border border-teal-600 bg-white px-4 py-2 text-sm font-semibold text-teal-700 hover:bg-teal-50">
                            Print Initial PR
                        </a>

                        <form action="{{ route('procurement.validate-signature', $procurement) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700" onclick="return confirm('Manager signature confirmed for Initial PR?')">
                                Validate Initial PR
                            </button>
                        </form>
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

    document.getElementById('multiScreenshotInput')?.addEventListener('change', function () {
        for (let i = 0; i < this.files.length; i++) {
            sharedFileBuffer.items.add(this.files[i]);
        }
        this.files = sharedFileBuffer.files;

        const count = this.files.length;
        const label = document.getElementById('fileCountInfo');
        if (label) {
            label.innerText = count > 0
                ? `${count} file(s) collected (ready to process)`
                : 'Click to select screenshots';
            if (count > 0) label.classList.add('text-teal-600');
        }
    });

    function transferFiles(form, buttonId) {
        if (sharedFileBuffer.files.length === 0) {
            alert('Please select screenshots first before scanning.');
            return false;
        }
        const hiddenInput = form.querySelector('.hidden-file-input');
        if (hiddenInput) hiddenInput.files = sharedFileBuffer.files;

        const btn = document.getElementById(buttonId);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Processing...';
        }
        return true;
    }

    // 🌟 TOGGLE PRODUCT DETAILS
    function toggleDetails(id) {
        const el = document.getElementById(id);
        el.classList.toggle('hidden');
    }
</script>
@endpush