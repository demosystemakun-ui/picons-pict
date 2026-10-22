@extends('layouts.app')

@section('title', 'Inventory Stock per Departemen')
@section('page-title', 'Department Stock')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    {{-- ═══ HEADER ═══ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Department Stock
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if(auth()->user()->hasRole('Super Admin'))
                    Pantau ketersediaan stok fisik di seluruh departemen.
                @else
                    Menampilkan stok untuk departemen <span class="font-medium text-gray-700 dark:text-gray-300">{{ auth()->user()->department->name ?? 'Anda' }}</span> dan <span class="font-medium text-gray-700 dark:text-gray-300">Office</span>.
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->hasRole('Super Admin'))
                <button type="button" onclick="openImportModal()"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3.5 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import
                </button>
            @endif

            <button type="button" onclick="openExportModal()"
                    class="inline-flex items-center gap-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3.5 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export
            </button>

            @if(auth()->user()->hasRole('Super Admin'))
                <a href="{{ route('inventory.stock.create') }}"
                   class="inline-flex items-center gap-2 rounded-md bg-slate-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-slate-900 shadow-sm hover:bg-slate-800 dark:hover:bg-gray-100 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Stok
                </a>
            @endif
        </div>
    </div>

    {{-- ═══ FILTER & SEARCH ═══ --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
        <form method="GET" action="{{ route('inventory.stock.index') }}" class="flex flex-wrap items-center gap-3">
            
            <div class="relative w-full sm:w-64">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang..." onchange="this.form.submit()"
                       class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 pl-9 pr-3 py-1.5 text-sm focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div class="relative w-full sm:w-auto">
                <select name="period_type" onchange="this.form.submit()"
                        class="w-full sm:w-auto appearance-none rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 pl-3 pr-8 py-1.5 text-sm text-gray-700 dark:text-gray-300 cursor-pointer focus:border-slate-500 focus:ring-slate-500">
                    <option value="daily"   {{ request('period_type', 'daily') == 'daily'   ? 'selected' : '' }}>Hari Ini</option>
                    <option value="monthly" {{ request('period_type') == 'monthly' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="yearly"  {{ request('period_type') == 'yearly'  ? 'selected' : '' }}>Tahun Ini</option>
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            @if(request('period_type') == 'monthly')
                <input type="month" name="period_value" value="{{ request('period_value', now()->format('Y-m')) }}" onchange="this.form.submit()"
                       class="w-full sm:w-auto rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-1.5 text-sm">
            @elseif(request('period_type') == 'yearly')
                <select name="period_value" onchange="this.form.submit()"
                        class="w-full sm:w-auto rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-1.5 text-sm cursor-pointer">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ request('period_value', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            @else
                <input type="date" name="period_value" value="{{ request('period_value', now()->format('Y-m-d')) }}" onchange="this.form.submit()"
                       class="w-full sm:w-auto rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-1.5 text-sm">
            @endif

            <div class="relative w-full sm:w-auto">
                <select name="stock_status" onchange="this.form.submit()"
                        class="w-full sm:w-auto appearance-none rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 pl-3 pr-8 py-1.5 text-sm cursor-pointer focus:border-slate-500 focus:ring-slate-500">
                    <option value="">Semua Status ({{ $stats['all'] ?? 0 }})</option>
                    <option value="out"       {{ request('stock_status') == 'out'       ? 'selected' : '' }}>Habis ({{ $stats['out'] ?? 0 }})</option>
                    <option value="critical"  {{ request('stock_status') == 'critical'  ? 'selected' : '' }}>Kritis ≤2 ({{ $stats['critical'] ?? 0 }})</option>
                    <option value="low"       {{ request('stock_status') == 'low'       ? 'selected' : '' }}>Rendah ≤5 ({{ $stats['low'] ?? 0 }})</option>
                    <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>Tersedia &gt;5 ({{ $stats['available'] ?? 0 }})</option>
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            @if(auth()->user()->hasRole('Super Admin'))
                <div class="relative w-full sm:w-auto">
                    <select name="department_id" onchange="this.form.submit()"
                            class="w-full sm:w-auto appearance-none rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 pl-3 pr-8 py-1.5 text-sm cursor-pointer focus:border-slate-500 focus:ring-slate-500">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            @endif

            @if(request('department_id') || request('period_value') || request('period_type') || request('search') || request('stock_status'))
                <a href="{{ route('inventory.stock.index') }}" class="inline-flex items-center justify-center rounded-md p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Reset filter">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            @endif
        </form>
    </div>

    {{-- ═══ TABEL DATA ═══ --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Departemen</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Barang / Item</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Stok Awal</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Terpakai</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Sisa Stok</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Update Terakhir</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
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
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $stock->department->name ?? '-' }}</div>
                                @if($isOfficeStock)
                                    <span class="inline-flex mt-0.5 items-center rounded border border-amber-200 bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">PUBLIC</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white max-w-[250px] truncate">{{ $stock->item->item_name ?? '-' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">{{ $stock->item->item_code ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300 font-medium">
                                {{ $initialStock }}
                            </td>
                            <td class="px-4 py-3 text-center text-amber-600 dark:text-amber-400 font-medium">
                                {{ $usedStock }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1.5 rounded-md border px-2 py-0.5 text-xs font-semibold
                                    @if($isCritical) bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800/50
                                    @elseif($isLow) bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800/50
                                    @else bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/50 @endif">
                                    {{ $currentStock }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-gray-500 dark:text-gray-400">
                                {{ $stock->updated_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if(!$isOut && $canUse)
                                        <a href="{{ route('inventory.stock.use.item', $stock->id) }}" class="inline-flex items-center gap-1 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition" title="Kurangi Stok">
                                            <svg class="h-3.5 w-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg>
                                            Pakai
                                        </a>
                                    @endif
                                    <a href="{{ route('inventory.stock.usage-detail', $stock->id) }}" class="inline-flex items-center gap-1 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition" title="Detail">
                                        <svg class="h-3.5 w-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                Belum ada data stok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stocks->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                {{ $stocks->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL IMPORT — hanya Super Admin --}}
@if(auth()->user()->hasRole('Super Admin'))
    @include('inventory.stock._import-modal')
@endif

{{-- MODAL EXPORT --}}
<div id="exportModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeExportModal()"></div>
    <div class="relative z-10 flex items-center justify-center min-h-full p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-xl border border-gray-200 dark:border-gray-700">
            <div class="flex items-start gap-3 mb-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Export Laporan Stok</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih rentang tanggal pemakaian.</p>
                </div>
            </div>
            <form action="{{ route('inventory.stock.export-usage') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ now()->format('Y-m-d') }}" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                </div>
                @if(auth()->user()->hasRole('Super Admin'))
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Departemen</label>
                        <select name="department_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:border-slate-500 focus:ring-slate-500">
                            <option value="">Semua Departemen</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeExportModal()" class="px-4 py-2 text-sm font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Batal</button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-slate-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-gray-100 transition">Download Excel</button>
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