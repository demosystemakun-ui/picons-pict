@extends('layouts.app')

@section('title', 'Vendor Comparison')
@section('page-title', 'Perbandingan Harga Vendor (E-Commerce)')

@php
    // Cek apakah sudah ada vendor terpilih
    $hasSelectedVendor = isset($procurement) && $comparisons->where('is_selected', true)->count() > 0;
@endphp

@section('content')
<div class="space-y-6 pb-12">

    {{-- ============================================================
         PAGE HEADER
    ============================================================ --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Vendor Comparison
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Bandingkan harga dan spesifikasi dari beberapa vendor e-commerce dalam satu tampilan.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start">
            {{-- ✅ Tombol Cetak PR Final — HANYA muncul jika sudah ada vendor terpilih --}}
            @if($hasSelectedVendor)
                <a href="{{ route('procurement.pdf.final', $procurement) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:from-teal-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak PR Final
                </a>
            @endif

            {{-- Tombol Kembali --}}
            <a href="{{ route('procurement.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar PR
            </a>
        </div>
    </div>

    {{-- ============================================================
         INFO DOKUMEN PROCUREMENT
    ============================================================ --}}
    @isset($procurement)
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-teal-50 to-white dark:from-teal-950/30 dark:to-gray-800 p-6 shadow-sm">
            <div class="pointer-events-none absolute -top-16 -right-16 h-48 w-48 rounded-full bg-teal-500/10 blur-3xl"></div>

            <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-100 dark:bg-teal-900/50 text-teal-600 dark:text-teal-400 shrink-0">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-teal-600 dark:text-teal-400">
                            No. Dokumen
                        </div>
                        <div class="mt-0.5 font-mono text-sm font-bold text-gray-900 dark:text-white">
                            {{ $procurement->no }}
                        </div>
                        <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                            <span class="text-gray-500 dark:text-gray-400">Item:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $procurement->item_name }}</span>
                            <span class="mx-2 text-gray-300 dark:text-gray-600">·</span>
                            <span class="text-gray-500 dark:text-gray-400">Qty:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $procurement->quantity }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-start sm:self-center">
                    @if($hasSelectedVendor)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300 ring-1 ring-inset ring-emerald-200 dark:ring-emerald-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Vendor Terpilih
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 dark:bg-amber-900/40 px-3 py-1 text-xs font-semibold text-amber-700 dark:text-amber-300 ring-1 ring-inset ring-amber-200 dark:ring-amber-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Belum Dipilih
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endisset

    {{-- ============================================================
         SUCCESS ALERT
    ============================================================ --}}
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/60 px-5 py-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- ============================================================
         ERROR ALERT
    ============================================================ --}}
    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800/60 px-5 py-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-rose-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-rose-800 dark:text-rose-300">
                        Terjadi kesalahan saat memproses permintaan
                    </h3>
                    <ul class="mt-2 list-disc list-inside space-y-0.5 text-sm text-rose-700 dark:text-rose-300/90">
                        @foreach ($errors->all() as $error)
                            <li class="break-words">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================
         UPLOAD & SCAN AI
    ============================================================ --}}
    @isset($procurement)
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Upload & Scan Screenshot Vendor</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Unggah beberapa screenshot sekaligus — AI akan mengekstrak data otomatis</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-teal-50 to-teal-100 dark:from-teal-900/30 dark:to-teal-800/30 px-3 py-1 text-xs font-semibold text-teal-700 dark:text-teal-300 ring-1 ring-inset ring-teal-200 dark:ring-teal-800">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    AI Gemini Vision
                </span>
            </header>

            <div class="p-6">
                <form action="{{ route('procurement.comparisons.store', $procurement) }}"
                      method="POST" enctype="multipart/form-data" class="space-y-5"
                      onsubmit="showLoadingState(this)">
                    @csrf

                    <label for="multiScreenshotInput"
                           class="group relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-teal-300 dark:border-teal-800/60 bg-teal-50/50 dark:bg-teal-950/20 px-6 py-8 text-center transition cursor-pointer hover:border-teal-400 hover:bg-teal-50 dark:hover:bg-teal-950/30">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 dark:bg-teal-900/50 text-teal-600 dark:text-teal-400 group-hover:scale-105 transition-transform">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p id="fileCountInfo" class="text-sm font-semibold text-gray-900 dark:text-white">
                                Klik untuk memilih screenshot
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Shopee, Tokopedia, Bukalapak, Lazada — bisa pilih beberapa sekaligus
                            </p>
                        </div>
                        <input type="file" name="screenshots[]" id="multiScreenshotInput"
                               accept="image/*" multiple required
                               class="sr-only">
                    </label>

                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            Mendukung JPG, PNG, dan format gambar lainnya.
                        </p>
                        <button type="submit" id="submitBtn"
                                class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition disabled:opacity-70 disabled:cursor-not-allowed">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Proses & Scan dengan AI
                        </button>
                    </div>
                </form>
            </div>
        </section>
    @endisset

    {{-- ============================================================
         TABEL PERBANDINGAN VENDOR
    ============================================================ --}}
    <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Rekapitulasi Perbandingan</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $comparisons->count() }} vendor terdaftar
                    </p>
                </div>
            </div>

            @if($hasSelectedVendor)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 dark:bg-emerald-900/30 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300 ring-1 ring-inset ring-emerald-200 dark:ring-emerald-800">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Vendor sudah dipilih
                </span>
            @endif
        </header>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/40">
                        @empty($procurement)
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">No. Dokumen</th>
                        @endempty
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vendor / Toko</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Produk</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Harga Satuan</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-center">Qty</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Rincian Biaya</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Total Akhir</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Metode</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Estimasi</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-center">Status</th>
                        @isset($procurement)
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Aksi</th>
                        @endisset
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70">
                    @forelse($comparisons as $comp)
                        @php $isSelected = (bool) $comp->is_selected; @endphp
                        <tr class="group transition-colors {{ $isSelected ? 'bg-teal-50/50 dark:bg-teal-950/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/30' }}">

                            @empty($procurement)
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700/60 px-2 py-1 text-xs font-mono font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $comp->procurementRequest->no ?? '-' }}
                                    </span>
                                </td>
                            @endempty

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $isSelected ? 'bg-teal-100 dark:bg-teal-900/50 text-teal-600 dark:text-teal-400' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300' }} shrink-0">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $comp->vendor_name }}
                                        </div>
                                        @if($isSelected)
                                            <div class="text-xs font-medium text-teal-600 dark:text-teal-400 mt-0.5">
                                                ★ Terpilih
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2 max-w-xs">
                                    {{ $comp->item_name }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <span class="text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    Rp {{ number_format($comp->price, 0, ',', '.') }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700/60 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    {{ $comp->quantity }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="space-y-0.5 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    <div>
                                        <span class="text-gray-400 dark:text-gray-500">Subtotal</span>
                                        <span class="ml-2 font-medium text-gray-700 dark:text-gray-300">
                                            Rp {{ number_format($comp->price * $comp->quantity, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 dark:text-gray-500">Ongkir</span>
                                        <span class="ml-2 font-medium text-gray-700 dark:text-gray-300">
                                            Rp {{ number_format($comp->shipping_cost, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 dark:text-gray-500">Layanan</span>
                                        <span class="ml-2 font-medium text-gray-700 dark:text-gray-300">
                                            Rp {{ number_format($comp->service_fee, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 dark:text-gray-500">PPN</span>
                                        <span class="ml-2 font-medium text-gray-700 dark:text-gray-300">
                                            Rp {{ number_format($comp->tax, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    @if($comp->discount_voucher > 0)
                                        <div>
                                            <span class="text-rose-500 dark:text-rose-400">Diskon</span>
                                            <span class="ml-2 font-medium text-rose-600 dark:text-rose-400">
                                                -Rp {{ number_format($comp->discount_voucher, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="text-base font-bold text-teal-600 dark:text-teal-400 whitespace-nowrap">
                                    Rp {{ number_format($comp->total_price, 0, ',', '.') }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $comp->payment_method ?? '-' }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $comp->estimated_delivery ?? '-' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if($isSelected)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-200 dark:ring-emerald-900/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Dipilih
                                    </span>
                                @elseif(isset($procurement))
                                    <form action="{{ route('procurement.comparisons.select', [$procurement, $comp]) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold text-teal-600 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-900/20 transition">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Pilih
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>

                            @isset($procurement)
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <form action="{{ route('procurement.comparisons.destroy', $comp) }}" method="POST"
                                          class="inline-block"
                                          onsubmit="return confirm('Hapus data vendor {{ $comp->vendor_name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center rounded-md p-1.5 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition"
                                                title="Hapus">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            @endisset
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ isset($procurement) ? 11 : 10 }}" class="px-6 py-16">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700/50 mb-4">
                                        <svg class="h-7 w-7 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Belum ada data perbandingan</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm">
                                        @isset($procurement)
                                            Unggah screenshot vendor di atas untuk mulai membandingkan harga.
                                        @else
                                            Belum ada perbandingan vendor yang tercatat dalam sistem.
                                        @endisset
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ============================================================
             FOOTER: Tombol Cetak PR Final (muncul jika sudah ada vendor terpilih)
        ============================================================ --}}
        @if($hasSelectedVendor)
            <footer class="px-6 py-5 border-t border-gray-100 dark:border-gray-700 bg-gradient-to-r from-teal-50/50 to-emerald-50/50 dark:from-teal-950/20 dark:to-emerald-950/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 shrink-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                            Vendor sudah dipilih
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                            Lanjutkan dengan mencetak PR Final yang berisi perbandingan vendor dan keputusan akhir.
                        </div>
                    </div>
                </div>
                <a href="{{ route('procurement.pdf.final', $procurement) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-teal-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition shrink-0">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak PR Final
                </a>
            </footer>
        @endif
    </section>
</div>
@endsection

@isset($procurement)
@push('scripts')
<script>
    let selectedFilesBuffer = new DataTransfer();

    document.getElementById('multiScreenshotInput').addEventListener('change', function (e) {
        const input = this;
        for (let i = 0; i < input.files.length; i++) {
            selectedFilesBuffer.items.add(input.files[i]);
        }
        input.files = selectedFilesBuffer.files;

        const count = input.files.length;
        const labelInfo = document.getElementById('fileCountInfo');
        if (labelInfo) {
            if (count > 0) {
                labelInfo.innerText = `${count} file screenshot terkumpul (siap diproses)`;
                labelInfo.classList.remove('text-gray-900', 'dark:text-white');
                labelInfo.classList.add('text-teal-600', 'dark:text-teal-400');
            } else {
                labelInfo.innerText = 'Klik untuk memilih screenshot';
            }
        }
    });

    function showLoadingState(form) {
        const btn = document.getElementById('submitBtn');
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
@endisset