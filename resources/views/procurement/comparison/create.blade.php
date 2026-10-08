@extends('layouts.app')

@section('title', 'Vendor Comparison')
@section('page-title', 'Perbandingan Harga Vendor')

@php
    $hasVendors = $procurement->vendors->count() > 0;
@endphp

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12">

    {{-- ═══ HEADER ═══ --}}
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

    {{-- ═══ SUCCESS ALERT ═══ --}}
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-5 py-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- ═══ WARNING ALERT ═══ --}}
    @if(session('warning'))
        <div class="rounded-xl bg-amber-50 border border-amber-200 px-5 py-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-sm font-medium text-amber-800">{{ session('warning') }}</p>
            </div>
        </div>
    @endif

    {{-- ═══ ERROR ALERT ═══ --}}
    @if(session('error'))
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-5 py-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-5 py-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-rose-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                </svg>
                <ul class="text-sm text-rose-700 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         AI SCAN SECTION — 2 Tombol (Monotaro + Marketplace Lain)
    ═══════════════════════════════════════════════════════════════ --}}
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
                    <p class="text-xs text-gray-500">Pilih jenis vendor, lalu upload screenshot-nya</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-teal-200 px-3 py-1 text-xs font-semibold text-teal-700">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
                AI Gemini Vision
            </span>
        </header>

        <div class="p-6 space-y-5">

            {{-- ═══ DROPZONE — shared file input ═══ --}}
            <label for="multiScreenshotInput"
                   class="group relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-teal-300 bg-teal-50/30 px-6 py-8 text-center transition cursor-pointer hover:border-teal-400 hover:bg-teal-50/60">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 text-teal-600 group-hover:scale-105 transition-transform">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p id="fileCountInfo" class="text-sm font-semibold text-gray-900">
                        Klik untuk memilih screenshot
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        Pilih file dulu, lalu klik tombol scan sesuai sumbernya
                    </p>
                </div>
                {{-- input file SHARED (bukan dalam form) --}}
                <input type="file" id="multiScreenshotInput"
                       accept="image/*" multiple
                       class="sr-only">
            </label>

            {{-- ═══ 2 TOMBOL SCAN ═══ --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                {{-- Tombol 1: Scan Monotaro --}}
                <form action="{{ route('procurement.comparisons.scan.monotaro', $procurement) }}"
                      method="POST" enctype="multipart/form-data"
                      onsubmit="return transferFiles(this, 'scanMonotaroBtn')">
                    @csrf
                    <input type="file" name="screenshots[]" multiple class="hidden-file-input" data-target="monotaro">
                    <button type="submit" id="scanMonotaroBtn"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-70 disabled:cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Scan Monotaro
                    </button>
                </form>

                {{-- Tombol 2: Scan Marketplace Lain --}}
                <form action="{{ route('procurement.comparisons.scan.others', $procurement) }}"
                      method="POST" enctype="multipart/form-data"
                      onsubmit="return transferFiles(this, 'scanOthersBtn')">
                    @csrf
                    <input type="file" name="screenshots[]" multiple class="hidden-file-input" data-target="others">
                    <button type="submit" id="scanOthersBtn"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-orange-500 to-rose-500 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:from-orange-600 hover:to-rose-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition disabled:opacity-70 disabled:cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Scan Marketplace Lain
                    </button>
                </form>
            </div>

            <p class="text-xs text-gray-400 text-center">
                <strong class="text-blue-600">Monotaro</strong>: khusus screenshot monotaro.id &nbsp;·&nbsp;
                <strong class="text-orange-600">Lain</strong>: Shopee, Tokopedia, Lazada, Bukalapak, Blibli
            </p>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════
         LIST VENDOR + FORM INPUT MANUAL
    ═══════════════════════════════════════════════════════════════ --}}
    <form action="{{ route('procurement.comparisons.store', $procurement) }}" method="POST"
          x-data="vendorForm()">
        @csrf

        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Data Vendor</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Bisa edit / tambah manual. Sistem otomatis pilih vendor termurah.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if($procurement->winnerVendor)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-700">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Winner: {{ $procurement->winnerVendor->vendor_name }}
                        </span>
                    @endif
                    <button type="button" @click="addVendor()"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Vendor
                    </button>
                </div>
            </header>

            <div class="p-6 space-y-4">

                {{-- Empty state --}}
                <template x-if="vendors.length === 0">
                    <div class="text-center py-8 rounded-xl border-2 border-dashed border-gray-200">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <p class="text-sm font-medium text-gray-700">Belum ada vendor</p>
                        <p class="mt-1 text-xs text-gray-500">Scan screenshot di atas, atau klik "Tambah Vendor" untuk input manual.</p>
                    </div>
                </template>

                {{-- Vendor cards --}}
                <template x-for="(v, idx) in vendors" :key="v.uid">
                    <div class="rounded-xl border-2 p-4 transition"
                         :class="isWinner(idx)
                             ? 'border-emerald-500 bg-emerald-50/40 shadow-sm'
                             : 'border-gray-200 bg-gray-50/50'">

                        {{-- Header card --}}
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-500"
                                      x-text="'Vendor #' + (idx + 1)"></span>
                                <template x-if="v.marketplace">
                                    <span class="inline-flex items-center rounded-md bg-orange-100 text-orange-700 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                          x-text="v.marketplace"></span>
                                </template>
                                <template x-if="isWinner(idx)">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                                        ✓ Termurah
                                    </span>
                                </template>
                            </div>
                            <button type="button" @click="removeVendor(idx)"
                                    class="text-xs text-rose-600 hover:underline">
                                Hapus
                            </button>
                        </div>

                        {{-- Form fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nama Vendor *</label>
                                <input type="text" :name="`vendors[${idx}][vendor_name]`" x-model="v.vendor_name"
                                       required placeholder="Monotaro / Shopee / dll"
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
                                       required min="0" step="100" placeholder="1000000"
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
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Company Name (opsional)</label>
                                <input type="text" :name="`vendors[${idx}][company_name]`" x-model="v.company_name"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Metode Pembayaran</label>
                                <input type="text" :name="`vendors[${idx}][payment_method]`" x-model="v.payment_method"
                                       placeholder="Transfer / COD / Virtual Account"
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
                                       placeholder="3-5 hari"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">URL Produk (opsional)</label>
                                <input type="url" :name="`vendors[${idx}][product_url]`" x-model="v.product_url"
                                       placeholder="https://shopee.co.id/..."
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                            </div>
                        </div>

                        {{-- Rincian biaya --}}
                        <div class="mt-4 rounded-lg bg-white border border-gray-200 p-3">
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

                        {{-- Detail Produk --}}
                        <template x-if="v.items && v.items.length > 0">
                            <div class="mt-4 rounded-lg bg-white border border-gray-200 overflow-hidden">
                                <div class="px-3 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Detail Produk (<span x-text="v.items.length"></span>)
                                    </span>
                                    <button type="button" @click="addItem(idx)"
                                            class="text-[10px] font-semibold text-teal-600 hover:underline">
                                        + Tambah
                                    </button>
                                </div>
                                <div class="divide-y divide-gray-100">
                                    <template x-for="(item, itemIdx) in v.items" :key="itemIdx">
                                        <div class="p-3 grid grid-cols-12 gap-2 items-center text-xs">
                                            <div class="col-span-5">
                                                <input type="text" :name="`vendors[${idx}][items][${itemIdx}][product_name]`"
                                                       x-model="item.product_name" placeholder="Nama produk"
                                                       class="w-full rounded-md border-gray-300 text-xs focus:border-teal-500 focus:ring-teal-500">
                                                <input type="text" :name="`vendors[${idx}][items][${itemIdx}][variant]`"
                                                       x-model="item.variant" placeholder="Varian (opsional)"
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
                                    class="mt-4 w-full text-xs text-gray-500 border border-dashed border-gray-300 rounded-lg py-2 hover:border-teal-400 hover:text-teal-600 transition">
                                + Tambah Detail Produk
                            </button>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Footer: Simpan + Cetak Final --}}
            <footer class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <p class="text-xs text-gray-500 order-2 sm:order-1">
                    Sistem otomatis set vendor dengan total harga <strong>terkecil</strong> sebagai pemenang.
                </p>
                <div class="flex items-center gap-2 order-1 sm:order-2">
                    @if($hasVendors && $procurement->winnerVendor)
                        <a href="{{ route('procurement.pdf.final', $procurement) }}"
                           target="_blank"
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
    /* ═══════════════════════════════════════════════════════════════
       Alpine component: vendorForm
    ═══════════════════════════════════════════════════════════════ */
    function vendorForm() {
        return {
            vendors: [],

            init() {
                const existing = @json($procurement->vendors ?? []);

                if (existing.length > 0) {
                    this.vendors = existing.map(v => ({
                        uid: this.newUid(),
                        vendor_name:        v.vendor_name        || '',
                        marketplace:        v.marketplace        || '',
                        company_name:       v.company_name       || '',
                        payment_method:     v.payment_method     || '',
                        payment_bank:       v.payment_bank       || '',
                        estimated_delivery: v.estimated_delivery || '',
                        product_url:        v.product_url        || '',
                        notes:              v.notes              || '',
                        subtotal:           parseFloat(v.subtotal)         || 0,
                        shipping_cost:      parseFloat(v.shipping_cost)    || 0,
                        discount_voucher:   parseFloat(v.discount_voucher) || 0,
                        tax_ppn:            parseFloat(v.tax_ppn)          || 0,
                        total_before_tax:   parseFloat(v.total_before_tax) || 0,
                        total_price:        parseFloat(v.total_price)      || 0,
                        tax_note:           v.tax_note || 'Include Tax',
                        items: (v.items || []).map(it => ({
                            product_name: it.product_name || '',
                            variant:      it.variant      || '',
                            sku:          it.sku          || '',
                            quantity:     parseInt(it.quantity)     || 1,
                            unit_price:   parseFloat(it.unit_price) || 0,
                            subtotal:     parseFloat(it.subtotal)   || 0,
                            stock_status: it.stock_status || '',
                            weight:       parseFloat(it.weight)     || 0,
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

    /* ═══════════════════════════════════════════════════════════════
       SHARED FILE BUFFER — 1 input, bisa dipakai 2 tombol scan
    ═══════════════════════════════════════════════════════════════ */
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
                    label.innerText = `${count} file terkumpul (siap diproses)`;
                    label.classList.remove('text-gray-900');
                    label.classList.add('text-teal-600');
                } else {
                    label.innerText = 'Klik untuk memilih screenshot';
                }
            }
        });
    });

    /* ═══════════════════════════════════════════════════════════════
       TRANSFER FILE KE FORM YANG DI-SUBMIT
    ═══════════════════════════════════════════════════════════════ */
    function transferFiles(form, buttonId) {
        if (sharedFileBuffer.files.length === 0) {
            alert('Silakan pilih screenshot dulu sebelum scan.');
            return false;
        }

        // Cari hidden input di dalam form ini
        const hiddenInput = form.querySelector('.hidden-file-input');
        if (hiddenInput) {
            hiddenInput.files = sharedFileBuffer.files;
        }

        // Loading state pada tombol
        const btn = document.getElementById(buttonId);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                AI sedang memproses...
            `;
        }

        return true;
    }
</script>
@endpush