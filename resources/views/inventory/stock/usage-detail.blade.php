@extends('layouts.app')

@section('title', 'Detail Pemakaian Stok')
@section('page-title', 'Usage Detail')

@section('content')
<div class="space-y-6 pb-12">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('inventory.stock.index') }}" class="hover:text-primary-600">Inventory</a>
                <span>/</span>
                <span>Detail Pemakaian</span>
            </div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                {{ $stock->item->item_name ?? 'Item' }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Kode: <span class="font-mono">{{ $stock->item->item_code ?? '-' }}</span>
                &middot; Departemen: <span class="font-medium">{{ $stock->department->name ?? '-' }}</span>
            </p>
        </div>

        <a href="{{ route('inventory.stock.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sisa Stok Saat Ini</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stock->stock }} Unit</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Dipakai (Periode Ini)</p>
            <p class="mt-2 text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $totalUsed }} Unit</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Dipakai (Sepanjang Waktu)</p>
            <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">{{ $totalUsedAllTime }} Unit</p>
        </div>
    </div>

    {{-- FILTER --}}
    <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <select name="period_type" onchange="this.form.submit()"
                        class="appearance-none rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 pl-3 pr-9 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 shadow-sm cursor-pointer">
                    <option value="daily"   {{ $periodType == 'daily'   ? 'selected' : '' }}>Harian</option>
                    <option value="monthly" {{ $periodType == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly"  {{ $periodType == 'yearly'  ? 'selected' : '' }}>Tahunan</option>
                    <option value="all"     {{ $periodType == 'all'     ? 'selected' : '' }}>Semua Waktu</option>
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            @if($periodType == 'monthly')
                <input type="month" name="period_value" value="{{ $periodValue }}" onchange="this.form.submit()"
                       class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-1.5 text-xs">
            @elseif($periodType == 'yearly')
                <select name="period_value" onchange="this.form.submit()"
                        class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-xs">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $periodValue == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            @elseif($periodType == 'daily')
                <input type="date" name="period_value" value="{{ $periodValue }}" onchange="this.form.submit()"
                       class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-1.5 text-xs">
            @endif
        </form>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- TABEL RIWAYAT --}}
        <section class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Riwayat Pemakaian</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Siapa yang memakai, kapan, dan berapa banyak.</p>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/40">
                            <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tanggal</th>
                            <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pemakai</th>
                            <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Departemen</th>
                            <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-center">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70">
                        @forelse($usages as $usage)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="px-6 py-3">
                                    <div class="text-xs font-medium text-gray-900 dark:text-white">
                                        {{ $usage->created_at->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $usage->created_at->format('H:i') }} WIB
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 text-xs font-bold">
                                            {{ strtoupper(substr($usage->user->name ?? '?', 0, 1)) }}
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $usage->user->name ?? 'User dihapus' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $usage->user->department->name ?? '-' }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:text-amber-400">
                                        -{{ $usage->quantity_used }} Unit
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada pemakaian di periode ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($usages->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $usages->links() }}
                </div>
            @endif
        </section>

        {{-- REKAP PER USER --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Rekap per Pemakai</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Total sepanjang waktu.</p>
            </header>
            <div class="p-4 space-y-3">
                @forelse($perUser as $row)
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 text-xs font-bold shrink-0">
                                {{ strtoupper(substr($row->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $row->user->name ?? 'User dihapus' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    {{ $row->user->department->name ?? '-' }}
                                </p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-amber-600 dark:text-amber-400 shrink-0">
                            {{ $row->total }} Unit
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-4">Belum ada data.</p>
                @endforelse
            </div>
        </section>

    </div>
</div>
@endsection