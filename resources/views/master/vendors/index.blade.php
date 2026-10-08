@extends('layouts.app')

@section('title', 'Vendor Comparison')
@section('page-title', 'Perbandingan Harga Vendor')

@php
    $hasVendors = $procurement->vendors->count() > 0;
@endphp

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Vendor Comparison</h1>
            <p class="mt-1 text-sm text-gray-500">
                PR No. <span class="font-mono font-medium text-gray-700">{{ $procurement->no }}</span>
                — {{ $procurement->description }}
            </p>
        </div>
        <a href="{{ route('procurement.show', $procurement) }}"
           class="inline-flex items-center gap-1.5 self-start rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Detail
        </a>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-5 py-4 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-5 py-4 text-sm font-medium text-rose-800">
            {{ session('error') }}
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

    {{-- AI SCAN --}}
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-teal-50 to-emerald-50/50 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-100 text-teal-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Scan Screenshot Vendor dengan AI</h2>
                    <p class="text-xs text-gray-500">Upload screenshot Shopee / Tokopedia / Monotaro — AI ekstrak otomatis</p>
                </div>
            </div>
        </header>

        <div class="p-6">
            <form action="{{ route('procurement.comparisons.scan', $procurement) }}"
                  method="POST" enctype="multipart/form-data" class="space-y-5"
                  onsubmit="showScanLoading(this)">
                @csrf

                <label for="multiScreenshotInput"
                       class="group relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-teal-300 bg-teal-50/30 px-6 py-8 text-center transition cursor-pointer hover:border-teal-400 hover:bg-teal-50/60">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 text-teal-600 group-hover:scale-105 transition-transform">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p id="fileCountInfo" class="text-sm font-semibold text-gray-900">Klik untuk memilih screenshot</p>
                        <p class="mt-1 text-xs text-gray-500">Bisa pilih beberapa file sekaligus</p>
                    </div>
                    <input type="file" name="screenshots[]" id="multiScreenshotInput"
                           accept="image/*" multiple required class="sr-only">
                </label>

                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <p class="text-xs text-gray-400">Mendukung JPG, PNG, WEBP.</p>
                    <button type="submit" id="scanBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-700 transition disabled:opacity-70 disabled:cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Scan dengan AI
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- FORM VENDORS --}}
    <form action="{{ route('procurement.comparisons.store', $procurement) }}" method="POST"
          x-data="vendorForm()">
        @csrf

        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Data Vendor</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Sistem otomatis pilih vendor termurah.</p>
                </div>
                <button type="button" @click="addVendor()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Vendor
                </button>
            </header>

            <div class="p-6 space-y-4">
                <template x-if="vendors.length === 0">
                    <div class="text-center py-8 rounded-xl border-2 border-dashed border-gray-200">
                        <p class="text-sm font-medium text-gray-700">Belum ada vendor</p>
                        <p class="mt-1 text-xs text-gray-500">Scan screenshot di atas, atau klik "Tambah Vendor".</p>
                    </div>
                </template>

                <template x-for="(v, idx) in vendors" :key="v.uid">
                    <div class="rounded-xl border-2 p-4 transition"
                         :class="isWinner(idx) ? 'border-emerald-500 bg-emerald-50/40' : 'border-gray-200 bg-gray-50/50'">

                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-3 mb-4 pb-3 border-b border-gray-200">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500"
                                          x-text="'Vendor #' + (idx + 1)"></span>
                                    <template x-if="v.marketplace">
                                        <span class="inline-flex items-center rounded-md bg-orange-100 text-orange-700 px-2 py-0.5 text-[10px] font-bold uppercase"
                                              x-text="v.marketplace"></span>
                                    </template>
                                    <template x-if="isWinner(idx)">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-bold uppercase text-white">
                                            ✓ Termurah
                                        </span>
                                    </template>
                                </div>
                                <h3 class="font-bold text-gray-900 text-base mt-1 truncate"
                                    x-text="v.vendor_name || 'Vendor tanpa nama'"></h3>
                            </div>
                            <button type="button" @click="removeVendor(idx)"
                                    class="text-xs text-rose-600 hover:underline shrink-0">Hapus</button>
                        </div>

                        {{-- Basic fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nama Vendor *</label>
                                <input type="text" :name="`vendors[${idx}][vendor_name]`" x-model="v.vendor_name" required
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Marketplace</label>
                                <input type="text" :name="`vendors[${idx}][marketplace]`" x-model="v.marketplace"
                                       placeholder="Shopee / Tokopedia / Monotaro"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Total Harga (Rp) *</label>
                                <input type="number" :name="`vendors[${idx}][total_price]`" x-model.number="v.total_price"
                                       required min="0" step="100"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan Pajak</label>
                                <select :name="`vendors[${idx}][tax_note]`" x-model="v.tax_note"
                                        class="w-full rounded-lg border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                                    <option value="Include Tax">Include Tax</option>
                                    <option value="Exclude Tax">Exclude Tax</option>
                                </select>
                            </div>
                        </div>

                        {{-- Rincian biaya --}}
                        <div class="rounded-lg bg-white border border-gray-200 p-3 mb-4">
                            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Rincian Biaya</div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                                <div>
                                    <span class="block text-gray-500 mb-0.5">Subtotal</span>
                                    <input type="number" :name="`vendors[${idx}][subtotal]`" x-model.number="v.subtotal"
                                           class="w-full rounded-md border-gray-300 text-xs focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div>
                                    <span class="block text-gray-500 mb-0.5">Ongkir</span>
                                    <input type="number" :name="`vendors[${idx}][shipping_cost]`" x-model.number="v.shipping_cost"
                                           class="w-full rounded-md border-gray-300 text-xs focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div>
                                    <span class="block text-gray-500 mb-0.5">Diskon</span>
                                    <input type="number" :name="`vendors[${idx}][discount_voucher]`" x-model.number="v.discount_voucher"
                                           class="w-full rounded-md border-gray-300 text-xs focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div>
                                    <span class="block text-gray-500 mb-0.5">PPN</span>
                                    <input type="number" :name="`vendors[${idx}][tax_ppn]`" x-model.number="v.tax_ppn"
                                           class="w-full rounded-md border-gray-300 text-xs focus:border-teal-500 focus:ring-teal-500">
                                </div>
                                <div>
                                    <span class="block text-gray-500 mb-0.5">Total Sebelum Pajak</span>
                                    <input type="number" :name="`vendors[${idx}][total_before_tax]`" x-model.number="v.total_before_tax"
                                           class="w-full rounded-md border-gray-300 text-xs focus:border-teal-500 focus:ring-teal-500">
                                </div>
                            </div>
                        </div>

                        {{-- Metode & pengiriman (3 kolom, tanpa alamat) --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Metode Pembayaran</label>
                                <input type="text" :name="`vendors[${idx}][payment_method]`" x-model="v.payment_method"
                                       placeholder="Transfer Bank / VA / COD"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Bank / Channel</label>
                                <input type="text" :name="`vendors[${idx}][payment_bank]`" x-model="v.payment_bank"
                                       placeholder="BCA Virtual Account"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Estimasi Pengiriman</label>
                                <input type="text" :name="`vendors[${idx}][estimated_delivery]`" x-model="v.estimated_delivery"
                                       placeholder="4-5 Sep"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            </div>
                        </div>

                        {{-- Detail produk --}}
                        <template x-if="v.items && v.items.length > 0">
                            <div class="rounded-lg bg-white border border-gray-200 overflow-hidden mb-4">
                                <div class="px-3 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Detail Produk (<span x-text="v.items.length"></span>)
                                    </span>
                                    <button type="button" @click="addItem(idx)"
                                            class="text-[10px] font-semibold text-teal-600 hover:underline">+ Tambah</button>
                                </div>
                                <div class="divide-y divide-gray-100">
                                    <template x-for="(item, itemIdx) in v.items" :key="itemIdx">
                                        <div class="p-3 grid grid-cols-12 gap-2 items-center text-xs">
                                            <div class="col-span-5">
                                                <input type="text" :name="`vendors[${idx}][items][${itemIdx}][product_name]`"
                                                       x-model="item.product_name" placeholder="Nama produk"
                                                       class="w-full rounded-md border-gray-300 text-xs focus:border-teal-500 focus:ring-teal-500">
                                                <input type="text" :name="`vendors[${idx}][items][${itemIdx}][variant]`"
                                                       x-model="item.variant" placeholder="Varian"
                                                       class="w-full mt-1 rounded-md border-gray-200 text-[10px] focus:border-teal-500 focus:ring-teal-500">
                                            </div>
                                            <div class="col-span-2">
                                                <input type="number" :name="`vendors[${idx}][items][${itemIdx}][quantity]`"
                                                       x-model.number="item.quantity" placeholder="Qty"
                                                       class="w-full rounded-md border-gray-300 text-xs text-center focus:border-teal-500 focus:ring-teal-500">
                                            </div>
                                            <div class="col-span-3">
                                                <input type="number" :name="`vendors[${idx}][items][${itemIdx}][unit_price]`"
                                                       x-model.number="item.unit_price" placeholder="Harga satuan"
                                                       class="w-full rounded-md border-gray-300 text-xs focus:border-teal-500 focus:ring-teal-500">
                                            </div>
                                            <div class="col-span-2 text-right font-semibold text-gray-700"
                                                 x-text="'Rp ' + ((item.quantity || 0) * (item.unit_price || 0)).toLocaleString('id-ID')"></div>
                                            <div class="col-span-12 text-right -mt-2">
                                                <button type="button" @click="removeItem(idx, itemIdx)"
                                                        class="text-[10px] text-rose-500 hover:underline">Hapus item</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="!v.items || v.items.length === 0">
                            <button type="button" @click="addItem(idx)"
                                    class="w-full text-xs text-gray-500 border border-dashed border-gray-300 rounded-lg py-2 hover:border-teal-400 hover:text-teal-600 transition">
                                + Tambah Detail Produk
                            </button>
                        </template>
                    </div>
                </template>
            </div>

            <footer class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <p class="text-xs text-gray-500 order-2 sm:order-1">
                    Sistem otomatis set vendor dengan total <strong>terkecil</strong> sebagai pemenang.
                </p>
                <div class="flex items-center gap-2 order-1 sm:order-2">
                    @if($hasVendors && $procurement->winnerVendor)
                        <a href="{{ route('procurement.pdf.final', $procurement) }}" target="_blank"
                           class="inline-flex items-center gap-2 rounded-lg border border-teal-600 bg-white px-4 py-2 text-sm font-semibold text-teal-700 hover:bg-teal-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"/>
                            </svg>
                            Cetak PR Final
                        </a>
                    @endif
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-5 py-2 text-sm font-semibold text-white hover:bg-teal-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Data Vendor
                    </button>
                </div>
            </footer>
        </section>
    </form>

</div>
@endsection

@push('scripts')
<script>
    function vendorForm() {
        return {
            vendors: [],

            init() {
                const existing = @json($procurement->vendors ?? []);

                if (existing.length > 0) {
                    this.vendors = existing.map(v => ({
                        uid: this.newUid(),
                        vendor_name: v.vendor_name || '',
                        marketplace: v.marketplace || '',
                        company_name: v.company_name || '',
                        payment_method: v.payment_method || '',
                        payment_bank: v.payment_bank || '',
                        estimated_delivery: v.estimated_delivery || '',
                        product_url: v.product_url || '',
                        notes: v.notes || '',
                        subtotal: parseFloat(v.subtotal) || 0,
                        shipping_cost: parseFloat(v.shipping_cost) || 0,
                        discount_voucher: parseFloat(v.discount_voucher) || 0,
                        tax_ppn: parseFloat(v.tax_ppn) || 0,
                        total_before_tax: parseFloat(v.total_before_tax) || 0,
                        total_price: parseFloat(v.total_price) || 0,
                        tax_note: v.tax_note || 'Include Tax',
                        items: (v.items || []).map(it => ({
                            product_name: it.product_name || '',
                            variant: it.variant || '',
                            sku: it.sku || '',
                            quantity: parseInt(it.quantity) || 1,
                            unit_price: parseFloat(it.unit_price) || 0,
                            subtotal: parseFloat(it.subtotal) || 0,
                            stock_status: it.stock_status || '',
                            weight: parseFloat(it.weight) || 0,
                        })),
                    }));
                } else {
                    this.addVendor();
                }
            },

            newUid() {
                return 'v-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8);
            },

            addVendor() {
                this.vendors.push({
                    uid: this.newUid(),
                    vendor_name: '',
                    marketplace: '',
                    company_name: '',
                    payment_method: '',
                    payment_bank: '',
                    estimated_delivery: '',
                    product_url: '',
                    notes: '',
                    subtotal: 0,
                    shipping_cost: 0,
                    discount_voucher: 0,
                    tax_ppn: 0,
                    total_before_tax: 0,
                    total_price: 0,
                    tax_note: 'Include Tax',
                    items: [],
                });
            },

            removeVendor(idx) {
                this.vendors.splice(idx, 1);
            },

            addItem(vendorIdx) {
                this.vendors[vendorIdx].items.push({
                    product_name: '',
                    variant: '',
                    sku: '',
                    quantity: 1,
                    unit_price: 0,
                    subtotal: 0,
                    stock_status: '',
                    weight: 0,
                });
            },

            removeItem(vendorIdx, itemIdx) {
                this.vendors[vendorIdx].items.splice(itemIdx, 1);
            },

            isWinner(idx) {
                const prices = this.vendors
                    .map((v, i) => ({ i, price: parseFloat(v.total_price) || 0 }))
                    .filter(x => x.price > 0);
                if (prices.length === 0) return false;
                const minPrice = Math.min(...prices.map(x => x.price));
                return parseFloat(this.vendors[idx].total_price) === minPrice;
            },
        };
    }

    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('multiScreenshotInput');
        if (!input) return;
        const buffer = new DataTransfer();
        input.addEventListener('change', () => {
            for (let i = 0; i < input.files.length; i++) buffer.items.add(input.files[i]);
            input.files = buffer.files;
            const count = input.files.length;
            const label = document.getElementById('fileCountInfo');
            if (label && count > 0) {
                label.innerText = `${count} file terkumpul (siap diproses)`;
                label.classList.add('text-teal-600');
            }
        });
    });

    function showScanLoading(form) {
        const btn = document.getElementById('scanBtn');
        if (!btn) return;
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            AI sedang memproses...
        `;
    }
</script>
@endpush