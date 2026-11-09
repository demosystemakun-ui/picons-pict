@extends('layouts.app')

@section('title', 'Purchase Order Detail')
@section('page-title', 'Purchase Order Detail')

@php
    use Carbon\Carbon;

    $pr          = $purchase->procurementRequest;
    $vendor      = $purchase->vendor;
    $items       = $purchase->items;
    $departments = \App\Models\Department::orderBy('name')->get(['id', 'name']);

    /* ═══ STATUS PO ═══ */
    $statusMeta = [
        'ordered'            => ['label' => 'Ordered',            'color' => 'bg-blue-500'],
        'partially_received' => ['label' => 'Partially Received', 'color' => 'bg-amber-500'],
        'completed'          => ['label' => 'Completed',          'color' => 'bg-emerald-500'],
    ];
    $currStatus = $purchase->status ?? 'ordered';
    $meta       = $statusMeta[$currStatus] ?? [
        'label' => ucwords(str_replace('_', ' ', $currStatus)),
        'color' => 'bg-gray-500',
    ];

    $fmtDate = fn ($d) => $d ? Carbon::parse($d)->format('d F Y') : '—';
    $rupiah  = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

    $grandTotal = $items->sum(fn ($it) => (float) $it->qty_ordered * (float) $it->unit_price);
    $canReceive = $currStatus !== 'completed';
@endphp

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12 pt-6 px-4">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="{{ route('procurement.purchases.index') }}"
               class="mt-0.5 inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition shrink-0">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-gray-900 dark:text-white font-mono">
                        {{ $purchase->po_number }}
                    </h1>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-md border border-gray-200 dark:border-gray-700">
                        <span class="h-1.5 w-1.5 rounded-full {{ $meta['color'] }}"></span>
                        {{ $meta['label'] }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Created on {{ $purchase->created_at->format('d M Y, H:i') }} WIB
                </p>
            </div>
        </div>

        @if($pr)
            <a href="{{ route('procurement.show', $pr) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition self-start">
                <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Lihat PR
            </a>
        @endif
    </div>

    {{-- VALIDATION ERRORS --}}
    @if($errors->any())
        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/60 px-4 py-3 text-sm text-red-800 dark:text-red-300">
            <p class="font-semibold mb-1">Data penerimaan belum valid:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- INFO CARD --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Informasi PO</h2>
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 text-sm">
            <div class="sm:col-span-4">
                <span class="text-xs text-gray-500 block mb-1">Vendor</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $vendor->vendor_name ?? '—' }}</span>
                @if(!empty($vendor?->marketplace))
                    <span class="block text-xs text-gray-500 mt-0.5">{{ $vendor->marketplace }}</span>
                @endif
            </div>
            <div class="sm:col-span-4">
                <span class="text-xs text-gray-500 block mb-1">PR Referensi</span>
                <span class="font-medium text-gray-900 dark:text-white font-mono">{{ $pr?->final_number ?? $pr?->no ?? '—' }}</span>
                @if($pr?->final_number)
                    <span class="block text-xs text-gray-500 mt-0.5">PR Awal: <span class="font-mono">{{ $pr->no }}</span></span>
                @endif
            </div>
            <div class="sm:col-span-4">
                <span class="text-xs text-gray-500 block mb-1">Deskripsi</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $pr?->description ?? '—' }}</span>
            </div>
            <div class="sm:col-span-4">
                <span class="text-xs text-gray-500 block mb-1">Tanggal Order</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $fmtDate($purchase->order_date) }}</span>
            </div>
            <div class="sm:col-span-4">
                <span class="text-xs text-gray-500 block mb-1">Estimasi Tiba</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $fmtDate($purchase->estimated_arrival) }}</span>
            </div>
            <div class="sm:col-span-4">
                <span class="text-xs text-gray-500 block mb-1">Total Order</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $rupiah($grandTotal) }}</span>
            </div>
            @if($purchase->notes)
                <div class="sm:col-span-12">
                    <span class="text-xs text-gray-500 block mb-1">Catatan</span>
                    <p class="text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $purchase->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ITEMS + RECEIVE FORM --}}
    <form action="{{ route('procurement.purchases.receive', $purchase) }}" method="POST" id="receiveForm" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 pb-3">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Item Pesanan</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 border-y border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide w-12">No.</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Item</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide text-right">Harga</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide text-center">Dipesan</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide text-center">Diterima</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide text-center">Rusak</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($items as $item)
                            @php $remaining = max(0, (int) $item->qty_ordered - (int) $item->qty_received); @endphp
                            <tr class="align-top">
                                <td class="py-3 px-4 text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                                <td class="py-3 px-4 font-medium text-gray-900 dark:text-white">{{ $item->product_name }}</td>
                                <td class="py-3 px-4 text-right text-gray-700 dark:text-gray-300">{{ $rupiah($item->unit_price) }}</td>
                                <td class="py-3 px-4 text-center font-semibold text-gray-900 dark:text-white">{{ $item->qty_ordered + 0 }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="{{ $remaining === 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-900 dark:text-white' }} font-semibold">{{ $item->qty_received + 0 }}</span>
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">{{ $item->qty_damaged + 0 }}</td>
                                <td class="py-3 px-4 text-right text-gray-900 dark:text-white">{{ $rupiah($item->qty_ordered * $item->unit_price) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 px-4 text-center text-gray-400 text-sm">Belum ada item pada PO ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50/70 dark:bg-gray-900/40 border-t border-gray-200 dark:border-gray-700">
                        <tr>
                            <td colspan="6" class="py-3 px-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</td>
                            <td class="py-3 px-4 text-right font-bold text-gray-900 dark:text-white">{{ $rupiah($grandTotal) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- RECEIVE SECTION --}}
        @if($canReceive && $items->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5">
                <div>
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Penerimaan Barang</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Isi jumlah barang yang datang pada penerimaan ini. Jika alokasi per departemen diisi, totalnya harus sama dengan Qty Datang.
                    </p>
                </div>

                @foreach($items as $i => $item)
                    @php $remaining = max(0, (int) $item->qty_ordered - (int) $item->qty_received); @endphp
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3" data-item="{{ $i }}">
                        <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">

                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item->product_name }}</p>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Sisa belum datang: <strong>{{ $remaining }}</strong></span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 max-w-md">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Qty Datang</label>
                                <input type="number" min="0" step="1"
                                       name="items[{{ $i }}][qty_received]"
                                       value="{{ old("items.$i.qty_received", $remaining) }}"
                                       data-role="qty-received"
                                       class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white p-2 focus:ring-2 focus:ring-slate-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Qty Rusak</label>
                                <input type="number" min="0" step="1"
                                       name="items[{{ $i }}][qty_damaged]"
                                       value="{{ old("items.$i.qty_damaged", 0) }}"
                                       class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white p-2 focus:ring-2 focus:ring-slate-500 focus:outline-none">
                            </div>
                        </div>

                        {{-- Alokasi per departemen (opsional) --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Alokasi per Departemen <span class="text-gray-400 font-normal">(opsional)</span></span>
                                <button type="button" onclick="addBreakdown({{ $i }})"
                                        class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold rounded border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <i data-lucide="plus" class="w-3 h-3"></i> Tambah Alokasi
                                </button>
                            </div>
                            <div id="breakdowns-{{ $i }}" class="space-y-2"></div>
                            <p id="breakdown-sum-{{ $i }}" class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 hidden"></p>
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-md bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                        <i data-lucide="package-check" class="w-4 h-4"></i> Simpan Penerimaan
                    </button>
                </div>
            </div>
        @elseif(!$canReceive)
            <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/60 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-300">
                Semua barang pada PO ini sudah diterima.
            </div>
        @endif
    </form>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    const DEPARTMENTS = @json($departments);
    const counters = {};

    function deptOptions() {
        return '<option value="">Pilih departemen</option>' +
            DEPARTMENTS.map(d => `<option value="${d.id}">${String(d.name).replace(/</g, '&lt;')}</option>`).join('');
    }

    function updateSum(itemIndex) {
        const wrap   = document.getElementById('breakdowns-' + itemIndex);
        const sumEl  = document.getElementById('breakdown-sum-' + itemIndex);
        const qtyEl  = document.querySelector(`[data-item="${itemIndex}"] [data-role="qty-received"]`);
        if (!wrap || !sumEl || !qtyEl) return;

        const inputs = wrap.querySelectorAll('input[type="number"]');
        if (inputs.length === 0) { sumEl.classList.add('hidden'); return; }

        let total = 0;
        inputs.forEach(i => total += parseInt(i.value || 0, 10));
        const target = parseInt(qtyEl.value || 0, 10);
        const ok = total === target;

        sumEl.classList.remove('hidden');
        sumEl.className = 'text-[11px] mt-1 ' + (ok ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400');
        sumEl.textContent = `Total alokasi: ${total} / Qty Datang: ${target}` + (ok ? '' : ' (harus sama)');
    }

    window.addBreakdown = function (itemIndex) {
        const wrap = document.getElementById('breakdowns-' + itemIndex);
        if (!wrap) return;

        counters[itemIndex] = (counters[itemIndex] ?? 0);
        const j = counters[itemIndex]++;

        const row = document.createElement('div');
        row.className = 'flex items-center gap-2';
        row.innerHTML = `
            <select name="items[${itemIndex}][breakdowns][${j}][department_id]" required
                    class="flex-1 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white p-2 focus:ring-2 focus:ring-slate-500 focus:outline-none">
                ${deptOptions()}
            </select>
            <input type="number" min="0" step="1" value="0" required
                   name="items[${itemIndex}][breakdowns][${j}][qty_received]"
                   class="w-24 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white p-2 focus:ring-2 focus:ring-slate-500 focus:outline-none">
            <button type="button" class="p-1.5 text-gray-400 hover:text-rose-600 rounded transition" title="Hapus">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>`;

        row.querySelector('button').addEventListener('click', () => { row.remove(); updateSum(itemIndex); });
        row.querySelector('input').addEventListener('input', () => updateSum(itemIndex));
        wrap.appendChild(row);
        updateSum(itemIndex);
    };

    // Update total saat Qty Datang berubah
    document.querySelectorAll('[data-role="qty-received"]').forEach(el => {
        const idx = el.closest('[data-item]').dataset.item;
        el.addEventListener('input', () => updateSum(idx));
    });

    // Cegah submit jika alokasi tidak sama dengan Qty Datang
    const form = document.getElementById('receiveForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            let valid = true;
            document.querySelectorAll('[data-item]').forEach(card => {
                const idx   = card.dataset.item;
                const wrap  = document.getElementById('breakdowns-' + idx);
                const inputs = wrap ? wrap.querySelectorAll('input[type="number"]') : [];
                if (inputs.length === 0) return;

                let total = 0;
                inputs.forEach(i => total += parseInt(i.value || 0, 10));
                const target = parseInt(card.querySelector('[data-role="qty-received"]').value || 0, 10);
                if (total !== target) valid = false;
            });

            if (!valid) {
                e.preventDefault();
                alert('Total alokasi per departemen harus sama dengan Qty Datang.');
            }
        });
    }
})();
</script>
@endpush
@endsection