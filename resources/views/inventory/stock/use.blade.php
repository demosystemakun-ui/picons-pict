@extends('layouts.app')

@section('title', 'Pemakaian Barang')
@section('page-title', 'Catat Pemakaian Barang')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-12">

    {{-- ============================================================
         PAGE HEADER
    ============================================================ --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Catat Pemakaian Barang
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Kurangi stok barang departemen sesuai pemakaian aktual.
            </p>
        </div>
        <a href="{{ route('inventory.stock.index') }}"
           class="hidden sm:inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    {{-- ============================================================
         ERROR ALERT
    ============================================================ --}}
    @if ($errors->any())
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/60 px-5 py-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">
                        Terdapat {{ $errors->count() }} kesalahan pada form
                    </h3>
                    <ul class="mt-2 list-disc list-inside space-y-0.5 text-sm text-red-700 dark:text-red-300/90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================
         MAIN CARD
    ============================================================ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

        {{-- Card Header --}}
        <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Form Pemakaian Stok</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Transaksi pengurangan stok barang</p>
                </div>
            </div>
        </header>

        <div class="p-6 space-y-6">

            {{-- ==========================================================
                 INFO BARANG
            ========================================================== --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 p-5">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Informasi Barang
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Departemen</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white text-right">
                            {{ $stock->department->name ?? '-' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Nama Barang</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white text-right">
                            {{ $stock->item->item_name ?? '-' }}
                        </span>
                    </div>

                    @if($stock->item->item_code ?? false)
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Kode Barang</span>
                            <span class="text-sm font-mono text-gray-700 dark:text-gray-300 text-right">
                                {{ $stock->item->item_code }}
                            </span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sisa Stok Saat Ini</span>
                        @php
                            $stockValue = (int) $stock->stock;
                            $isCritical = $stockValue <= 2;
                            $isLow = $stockValue <= 5;
                        @endphp
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-bold
                            {{ $isCritical
                                ? 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-200 dark:ring-red-900/60'
                                : ($isLow
                                    ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 ring-1 ring-inset ring-amber-200 dark:ring-amber-900/60'
                                    : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-200 dark:ring-emerald-900/60') }}">
                            @if($isCritical)
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500 animate-pulse"></span>
                            @endif
                            {{ $stockValue }} Unit
                        </span>
                    </div>
                </div>
            </div>

            {{-- ==========================================================
                 FORM PEMAKAIAN
            ========================================================== --}}
            <form action="{{ route('inventory.stock.process-use', $stock->id) }}" method="POST" class="space-y-5" id="usageForm">
                @csrf

                <div>
                    <label for="used_stock" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                        Jumlah yang Dipakai <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <input type="number" id="used_stock" name="used_stock"
                               min="1" max="{{ $stock->stock }}"
                               value="{{ old('used_stock') }}" required
                               placeholder="0"
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 pl-3 pr-16 py-2.5 text-sm text-gray-900 dark:text-white shadow-sm placeholder:text-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-gray-400 dark:text-gray-500">
                            Unit
                        </span>
                    </div>

                    {{-- Quick select --}}
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="text-xs text-gray-400 dark:text-gray-500">Cepat:</span>
                        @foreach([1, 5, 10] as $qty)
                            @if($qty <= $stockValue)
                                <button type="button"
                                        onclick="document.getElementById('used_stock').value = {{ $qty }}"
                                        class="rounded-md border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-700 dark:hover:text-primary-300 transition">
                                    {{ $qty }}
                                </button>
                            @endif
                        @endforeach
                        <button type="button"
                                onclick="document.getElementById('used_stock').value = {{ $stockValue }}"
                                class="rounded-md border border-red-200 dark:border-red-900/60 bg-white dark:bg-gray-800 px-2.5 py-1 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                            Semua ({{ $stockValue }})
                        </button>
                    </div>

                    @error('used_stock')
                        <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Warning akan stok habis --}}
                @if($isCritical)
                    <div class="rounded-lg border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-900/20 px-4 py-3">
                        <div class="flex gap-3">
                            <svg class="h-5 w-5 flex-shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                                Stok barang ini <span class="font-semibold">tersisa sedikit</span>. Pastikan jumlah pemakaian sudah sesuai sebelum menyimpan.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Footer Actions --}}
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('inventory.stock.index') }}"
                       class="inline-flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                        </svg>
                        Simpan & Kurangi Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection