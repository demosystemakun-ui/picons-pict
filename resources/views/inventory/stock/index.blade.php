@extends('layouts.app')

@section('title', 'Inventory Stock per Departemen')
@section('page-title', 'Department Stock Items')

@section('content')
<div class="space-y-6 pb-12">

    {{-- ═══ HEADER ═══ --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Department Stock Items
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if(auth()->user()->hasRole('Super Admin'))
                    Monitor physical stock availability across all departments.
                @else
                    Menampilkan stok departemen
                    <span class="font-semibold text-primary-600 dark:text-primary-400">{{ auth()->user()->department->name ?? 'My Department' }}</span>
                    dan
                    <span class="font-semibold text-amber-600 dark:text-amber-400">Office</span> (dapat dipakai semua user).
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start">
            @if(auth()->user()->hasRole('Super Admin'))
                <button type="button" onclick="openImportModal()"
                        class="inline-flex items-center gap-2 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 px-4 py-2 text-sm font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import Excel
                </button>
            @endif

            <button type="button" onclick="openExportModal()"
                    class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 px-4 py-2 text-sm font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>

            @if(auth()->user()->hasRole('Super Admin'))
                <a href="{{ route('inventory.stock.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Stok
                </a>
            @endif
        </div>
    </div>

    {{-- ═══ MAIN CARD ═══ --}}
    <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        
        {{-- HEADER & FILTER --}}
        <header class="px-5 sm:px-6 py-5 border-b border-gray-100 dark:border-gray-700 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 shrink-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">
                            @if(auth()->user()->hasRole('Super Admin'))
                                Physical Stock List
                            @else
                                Stok Departemen
                            @endif
                        </h2>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">
                            Total {{ $stocks->total() ?? $stocks->count() }} entri stok tercatat
                        </p>
                    </div>
                </div>
            </div>

            {{-- FORM FILTER --}}
            <form method="GET" action="{{ route('inventory.stock.index') }}" class="flex flex-wrap items-center gap-2">
                
                {{-- SEARCH --}}
                <div class="relative w-full sm:w-auto">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode / dept…" onchange="this.form.submit()"
                           class="w-full sm:w-60 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 pl-10 pr-3 py-2 text-xs font-medium focus:border-primary-500 focus:ring-primary-500 transition-colors">
                </div>

                {{-- PERIODE --}}
                <div class="relative w-full sm:w-auto">
                    <select name="period_type" onchange="this.form.submit()"
                            class="w-full sm:w-auto appearance-none rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 pl-3 pr-10 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer focus:border-primary-500 focus:ring-primary-500">
                        <option value="daily"   {{ request('period_type', 'daily') == 'daily'   ? 'selected' : '' }}>Harian</option>
                        <option value="monthly" {{ request('period_type') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                        <option value="yearly"  {{ request('period_type') == 'yearly'  ? 'selected' : '' }}>Tahunan</option>
                    </select>
                    <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                @if(request('period_type') == 'monthly')
                    <input type="month" name="period_value" value="{{ request('period_value', now()->format('Y-m')) }}" onchange="this.form.submit()"
                           class="w-full sm:w-auto rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs font-medium focus:border-primary-500 focus:ring-primary-500">
                @elseif(request('period_type') == 'yearly')
                    <select name="period_value" onchange="this.form.submit()"
                            class="w-full sm:w-auto appearance-none rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs font-medium cursor-pointer focus:border-primary-500 focus:ring-primary-500">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ request('period_value', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                @else
                    <input type="date" name="period_value" value="{{ request('period_value', now()->format('Y-m-d')) }}" onchange="this.form.submit()"
                           class="w-full sm:w-auto rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-xs font-medium focus:border-primary-500 focus:ring-primary-500">
                @endif

                {{-- FILTER STATUS STOK --}}
                <div class="relative w-full sm:w-auto">
                    <select name="stock_status" onchange="this.form.submit()"
                            class="w-full sm:w-auto appearance-none rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 pl-3 pr-10 py-2 text-xs font-medium cursor-pointer focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Semua Status ({{ $stats['all'] ?? 0 }})</option>
                        <option value="out"       {{ request('stock_status') == 'out'       ? 'selected' : '' }}>Habis ({{ $stats['out'] ?? 0 }})</option>
                        <option value="critical"  {{ request('stock_status') == 'critical'  ? 'selected' : '' }}>Kritis ≤2 ({{ $stats['critical'] ?? 0 }})</option>
                        <option value="low"       {{ request('stock_status') == 'low'       ? 'selected' : '' }}>Rendah ≤5 ({{ $stats['low'] ?? 0 }})</option>
                        <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>Banyak &gt;5 ({{ $stats['available'] ?? 0 }})</option>
                    </select>
                    <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                @if(auth()->user()->hasRole('Super Admin'))
                    <div class="relative w-full sm:w-auto">
                        <select name="department_id" onchange="this.form.submit()"
                                class="w-full sm:w-auto appearance-none rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 pl-3 pr-10 py-2 text-xs font-medium cursor-pointer focus:border-primary-500 focus:ring-primary-500">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                @endif

                @if(request('department_id') || request('period_value') || request('period_type') || request('search') || request('stock_status'))
                    <a href="{{ route('inventory.stock.index') }}" class="w-full sm:w-auto flex items-center justify-center rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-900/20 p-2 text-rose-600 hover:bg-rose-100 transition" title="Reset filter">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="ml-1 text-xs font-semibold sm:hidden">Reset</span>
                    </a>
                @endif
            </form>
        </header>

        {{-- ═══ DESKTOP TABLE (Tampil di layar md ke atas) ═══ --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/40">
                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Departemen</th>
                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Barang / Item</th>
                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-center">Sisa Stok</th>
                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Terakhir Update</th>
                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70">
                    @forelse($stocks as $stock)
                        @php
                            $usedStock    = (int) ($stock->used_stock ?? 0);
                            $currentStock = (int) $stock->stock;
                            $initialStock = $currentStock + $usedStock;

                            $isLow      = $currentStock <= 5;
                            $isCritical = $currentStock <= 2;
                            $isOut      = $currentStock <= 0;

                            $isOfficeStock = strtolower($stock->department->name ?? '') === 'office';
                            $isOwnDept     = (int) $stock->department_id === (int) auth()->user()->department_id;

                            $canUse = !$isOut && (
                                auth()->user()->hasRole('Super Admin')
                                || auth()->user()->hasRole('Admin')
                                || $isOwnDept
                                || $isOfficeStock
                            );
                        @endphp
                        <tr class="group hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 shrink-0">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <span class="font-semibold text-gray-900 dark:text-white text-[13px]">
                                        {{ $stock->department->name ?? '-' }}
                                        @if($isOfficeStock)
                                            <span class="ml-1 inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-400">PUBLIC</span>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-900 dark:text-white truncate max-w-[250px]">
                                    {{ $stock->item->item_name ?? '-' }}
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 font-mono mt-0.5">
                                    {{ $stock->item->item_code ?? 'No Code' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex flex-col items-center">
                                    @if($isCritical)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 dark:bg-red-500/10 px-3 py-1 text-xs font-bold text-red-700 dark:text-red-400 border border-red-100 dark:border-red-900/30">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                            {{ $currentStock }} Unit
                                        </span>
                                    @elseif($isLow)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 dark:bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            {{ $currentStock }} Unit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $currentStock }} Unit
                                        </span>
                                    @endif
                                    <div class="text-[10px] text-gray-400 mt-1">Total Awal: {{ $initialStock }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-[13px] font-medium text-gray-700 dark:text-gray-300">{{ $stock->updated_at->format('d M Y') }}</div>
                                <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $stock->updated_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($isOut)
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-1.5 text-xs font-bold text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                            Habis
                                        </span>
                                    @elseif($canUse)
                                        <a href="{{ route('inventory.stock.use.item', $stock->id) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 dark:border-red-900/60 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-bold text-red-600 dark:text-red-400 shadow-sm hover:bg-red-50 transition">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg>
                                            Pakai
                                        </a>
                                    @endif
                                    <a href="{{ route('inventory.stock.usage-detail', $stock->id) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 dark:border-blue-900/60 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-bold text-blue-600 dark:text-blue-400 shadow-sm hover:bg-blue-50 transition">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Belum ada data stok</p>
                                <p class="mt-1 text-xs text-gray-500">Tidak ada hasil untuk filter yang dipilih.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ═══ MOBILE CARD VIEW (Tampil di layar kecil) ═══ --}}
        <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($stocks as $stock)
                @php
                    $usedStock    = (int) ($stock->used_stock ?? 0);
                    $currentStock = (int) $stock->stock;
                    $initialStock = $currentStock + $usedStock;

                    $isLow      = $currentStock <= 5;
                    $isCritical = $currentStock <= 2;
                    $isOut      = $currentStock <= 0;

                    $isOfficeStock = strtolower($stock->department->name ?? '') === 'office';
                    $isOwnDept     = (int) $stock->department_id === (int) auth()->user()->department_id;

                    $canUse = !$isOut && (
                        auth()->user()->hasRole('Super Admin')
                        || auth()->user()->hasRole('Admin')
                        || $isOwnDept
                        || $isOfficeStock
                    );
                @endphp
                <div class="p-4 space-y-3">
                    {{-- Header Mobile --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <div class="flex h-7 w-7 items-center justify-center rounded-md bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 shrink-0">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">{{ $stock->department->name ?? '-' }}</div>
                                @if($isOfficeStock)
                                    <span class="inline-flex mt-0.5 items-center rounded bg-amber-50 dark:bg-amber-500/10 px-1.5 py-0.5 text-[9px] font-bold text-amber-700 dark:text-amber-400">PUBLIC</span>
                                @endif
                            </div>
                        </div>
                        @if($isCritical)
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold text-red-700 border border-red-100">Kritis</span>
                        @elseif($isLow)
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-100">Rendah</span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-100">Aman</span>
                        @endif
                    </div>

                    {{-- Item Mobile --}}
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">{{ $stock->item->item_name ?? '-' }}</h3>
                        <p class="text-[11px] text-gray-500 font-mono mt-0.5">{{ $stock->item->item_code ?? 'No Code' }}</p>
                    </div>

                    {{-- Info Mobile --}}
                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                        <div class="text-center">
                            <div class="text-[10px] text-gray-500 uppercase font-semibold">Awal</div>
                            <div class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $initialStock }}</div>
                        </div>
                        <div class="h-6 w-px bg-gray-200 dark:bg-gray-700"></div>
                        <div class="text-center">
                            <div class="text-[10px] text-gray-500 uppercase font-semibold">Terpakai</div>
                            <div class="text-xs font-bold text-amber-600">{{ $usedStock }}</div>
                        </div>
                        <div class="h-6 w-px bg-gray-200 dark:bg-gray-700"></div>
                        <div class="text-center">
                            <div class="text-[10px] text-gray-500 uppercase font-semibold">Sisa</div>
                            <div class="text-sm font-black {{ $isCritical ? 'text-red-600' : ($isLow ? 'text-amber-600' : 'text-emerald-600') }}">{{ $currentStock }}</div>
                        </div>
                    </div>

                    {{-- Aksi Mobile --}}
                    <div class="flex items-center gap-2 pt-1">
                        @if(!$isOut && $canUse)
                            <a href="{{ route('inventory.stock.use.item', $stock->id) }}" class="flex-1 inline-flex justify-center items-center gap-1.5 rounded-lg bg-red-50 text-red-600 border border-red-200 px-3 py-2 text-xs font-bold hover:bg-red-100 transition">
                                Pakai Stok
                            </a>
                        @elseif($isOut)
                            <button disabled class="flex-1 inline-flex justify-center items-center gap-1.5 rounded-lg bg-gray-100 text-gray-400 px-3 py-2 text-xs font-bold cursor-not-allowed">
                                Stok Habis
                            </button>
                        @endif
                        <a href="{{ route('inventory.stock.usage-detail', $stock->id) }}" class="flex-1 inline-flex justify-center items-center gap-1.5 rounded-lg bg-blue-50 text-blue-600 border border-blue-200 px-3 py-2 text-xs font-bold hover:bg-blue-100 transition">
                            Detail
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <p class="text-sm font-semibold">Belum ada data stok</p>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if($stocks->hasPages())
            <div class="px-5 sm:px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50">
                {{ $stocks->links() }}
            </div>
        @endif
    </section>
</div>

{{-- MODAL IMPORT — hanya Super Admin --}}
@if(auth()->user()->hasRole('Super Admin'))
    @include('inventory.stock._import-modal')
@endif

{{-- MODAL EXPORT --}}
<div id="exportModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeExportModal()"></div>
    <div class="relative z-10 flex items-center justify-center min-h-full p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700">
            <div class="flex items-start gap-3 mb-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Export Stock Usage</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih rentang tanggal untuk export laporan pemakaian.</p>
                </div>
            </div>
            <form action="{{ route('inventory.stock.export-usage') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1.5">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1.5">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ now()->format('Y-m-d') }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm">
                    </div>
                </div>
                @if(auth()->user()->hasRole('Super Admin'))
                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1.5">Departemen</label>
                        <select name="department_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm">
                            <option value="">Semua Departemen</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeExportModal()" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Batal</button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition">Download Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openExportModal() {
        document.getElementById('exportModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeExportModal() {
        document.getElementById('exportModal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeExportModal(); });
</script>
@endpush
@endsection