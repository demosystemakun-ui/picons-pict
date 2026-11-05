@extends('layouts.app')

@section('title', 'Vendor Comparison')
@section('page-title', 'Vendor Price Comparison')

@php
    $hasVendors = $procurement->vendors->count() > 0;
    $isFinalApproved = in_array($procurement->status, ['final_approved', 'received', 'completed']);
    $isProcessed = $procurement->status === 'processed'; // PR Awal sudah divalidasi
@endphp

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12">

    {{-- ═══ HEADER ═══ --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Vendor Comparison</h1>
            <p class="mt-1 text-sm text-gray-500">
                PR Awal: <span class="font-mono font-medium text-gray-700">{{ $procurement->no }}</span>
                @if($procurement->final_number)
                    | PR Final: <span class="font-mono font-medium text-indigo-600">{{ $procurement->final_number }}</span>
                @endif
                — {{ $procurement->description }}
            </p>
        </div>
        <a href="{{ route('procurement.show', $procurement) }}"
           class="inline-flex items-center gap-1.5 self-start rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Detail
        </a>
    </div>

    {{-- ═══ SUCCESS / ERROR ALERTS ═══ --}}
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-5 py-4">
            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-5 py-4">
            <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-5 py-4">
            <ul class="text-sm text-rose-700 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         AI SCAN SECTION (Hanya jika status approved)
    ═══════════════════════════════════════════════════════════════ --}}
    @if($procurement->status === 'approved')
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-teal-50 to-emerald-50/50 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-100 text-teal-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Scan Vendor Screenshots with AI</h2>
                    <p class="text-xs text-gray-500">Select files, then click the scan button</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-teal-200 px-3 py-1 text-xs font-semibold text-teal-700">
                AI Gemini Vision
            </span>
        </header>

        <div class="p-6 space-y-5">
            <label for="multiScreenshotInput"
                   class="group relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-teal-300 bg-teal-50/30 px-6 py-8 text-center transition cursor-pointer hover:border-teal-400 hover:bg-teal-50/60">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 text-teal-600 group-hover:scale-105 transition-transform">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p id="fileCountInfo" class="text-sm font-semibold text-gray-900">Click to select screenshots</p>
                    <p class="mt-1 text-xs text-gray-500">Shopee, Tokopedia, Bukalapak, Lazada — multiple files allowed</p>
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

    {{-- ═══════════════════════════════════════════════════════════════
         VENDOR COMPARISON TABLE
    ═══════════════════════════════════════════════════════════════ --}}
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Comparison Recap</h2>
                <p class="text-xs text-gray-500">{{ $procurement->vendors->count() }} vendors registered</p>
            </div>
        </header>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/80">
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500">Vendor / Store</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">Subtotal</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">Shipping</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">Discount</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">Tax</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">Total</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-center">Status</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($procurement->vendors as $vendor)
                        @php $isSelected = (bool) $vendor->is_winner; @endphp
                        <tr class="{{ $isSelected ? 'bg-teal-50/50' : 'hover:bg-gray-50' }}">
                            <td class="px-5 py-4 font-semibold">{{ $vendor->vendor_name }}</td>
                            <td class="px-5 py-4 text-right">Rp {{ number_format($vendor->subtotal, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right">Rp {{ number_format($vendor->shipping_cost, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right text-rose-600">- Rp {{ number_format($vendor->discount_voucher, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right">Rp {{ number_format($vendor->tax_ppn, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right font-bold text-teal-600">Rp {{ number_format($vendor->total_price, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-center">
                                @if($isSelected)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Selected</span>
                                @elseif($procurement->status === 'approved')
                                    <form action="{{ route('procurement.comparisons.select', [$procurement, $vendor]) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="rounded-md px-2.5 py-1 text-xs font-semibold text-teal-600 hover:bg-teal-50">Select</button>
                                    </form>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if($procurement->status === 'approved')
                                    <form action="{{ route('procurement.comparisons.destroy', $vendor) }}" method="POST" onsubmit="return confirm('Delete vendor?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">Locked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-16 text-center text-gray-500">No comparison data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ═══ FOOTER: Aksi Berdasarkan Status ═══ --}}
        @if($hasVendors && $procurement->winnerVendor)
            <footer class="px-6 py-5 border-t border-gray-100 bg-gradient-to-r from-teal-50/50 to-emerald-50/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold text-gray-900">
                        @if($isFinalApproved)
                            Final PR Validated
                        @elseif($isProcessed)
                            PR Awal Validated — Ready for Final PR
                        @else
                            Vendor Selected — Waiting for PR Awal Validation
                        @endif
                    </div>
                    <div class="text-xs text-gray-600">
                        @if($isFinalApproved)
                            You can now proceed to the PO/Cash Advance/Reimbursement process.
                        @elseif($isProcessed)
                            Fill in the Final PR form (Budget, Date, Request By, etc.) to generate the PDF.
                        @else
                            Print the PR Awal, get Manager signature, then validate.
                        @endif
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-2">
                    @if($isFinalApproved)
                        {{-- Tombol Lanjutan berdasarkan procurement_method --}}
                        @if($procurement->procurement_method === 'po')
                            <a href="{{ route('procurement.purchases.create', $procurement) }}" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Create PO</a>
                        @elseif($procurement->procurement_method === 'cash_advance')
                            <a href="{{ route('cash-advances.create', ['procurement_request_id' => $procurement->id]) }}" class="rounded-lg bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">Create Cash Advance</a>
                        @elseif($procurement->procurement_method === 'reimbursement')
                            <a href="{{ route('reimbursement.create', ['procurement_request_id' => $procurement->id]) }}" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Create Reimbursement</a>
                        @endif

                        {{-- Tombol View PDF --}}
                        <a href="{{ route('procurement.pdf.final', $procurement) }}" target="_blank" class="rounded-lg border border-teal-600 bg-white px-4 py-2 text-sm font-semibold text-teal-700 hover:bg-teal-50">
                            View Final PR PDF
                        </a>

                    @elseif($isProcessed)
                        {{-- ═══ TOMBOL MASUK KE FORM FINAL PR ═══ --}}
                        <a href="{{ route('procurement.comparisons.final-pr.create', $procurement) }}"
                           class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-indigo-700 hover:to-purple-700 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Create Final PR
                        </a>
                    @else
                        {{-- Print PR Awal & Tombol Validasi Pertama --}}
                        <a href="{{ route('procurement.pdf', $procurement) }}" target="_blank" class="rounded-lg border border-teal-600 bg-white px-4 py-2 text-sm font-semibold text-teal-700 hover:bg-teal-50">
                            Print PR Awal
                        </a>

                        <form action="{{ route('procurement.validate-signature', $procurement) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700" onclick="return confirm('Manager signature confirmed for PR Awal?')">
                                Validate PR Awal
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
</script>
@endpush