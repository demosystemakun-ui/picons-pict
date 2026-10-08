@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6 pb-12">

    {{-- ============================================================
         HERO BANNER
    ============================================================ --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-900 via-blue-950 to-blue-900 px-6 py-8 text-white shadow-lg ring-1 ring-white/5">
        <div class="pointer-events-none absolute inset-0 opacity-[0.07]">
            <svg class="h-full w-full" viewBox="0 0 400 200" preserveAspectRatio="none">
                <path d="M0 200 L120 40 L240 200 Z" fill="white"></path>
                <path d="M180 200 L320 20 L400 200 Z" fill="white"></path>
            </svg>
        </div>
        <div class="pointer-events-none absolute -top-24 -right-24 h-64 w-64 rounded-full bg-primary-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-16 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
               
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Dashboard</h1>
                <p class="mt-1.5 text-sm text-slate-300 max-w-lg">
                    Real-time overview of the company's stock, requests, and procurement activities.
                </p>
            </div>
            <div class="flex items-center gap-1.5 text-sm text-slate-400">
                <i data-lucide="home" class="w-4 h-4"></i>
                <span>/</span>
                <span class="text-white font-medium">Dashboard</span>
            </div>
        </div>
    </div>

    {{-- ============================================================
         STAT CARDS
    ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Inventory Summary --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            @php
                $miniStats = [
                    ['label' => 'Total Item',       'value' => $stats['total_item']       ?? 0, 'icon' => 'package',       'color' => 'blue',   'url' => route('items.index')],
                    ['label' => 'Total Stock',      'value' => $stats['total_stock']      ?? 0, 'icon' => 'boxes',         'color' => 'indigo', 'url' => route('inventory.stock.index')],
                    ['label' => 'Purchase Order',   'value' => $stats['purchase_order']   ?? 0, 'icon' => 'shopping-cart', 'color' => 'purple', 'url' => '#'],
                    ['label' => 'Goods Received',   'value' => $stats['goods_received']   ?? 0, 'icon' => 'truck',         'color' => 'green',  'url' => '#'],
                ];

                if (auth()->check() && auth()->user()->hasRole('Super Admin')) {
                    $miniStats[] = ['label' => 'Total Vendor', 'value' => $stats['total_vendor'] ?? 0, 'icon' => 'building-2', 'color' => 'teal', 'url' => route('vendors.index')];
                }

                $colorMap = [
                    'blue'   => ['bg' => 'bg-blue-50 dark:bg-blue-500/10',     'text' => 'text-blue-600 dark:text-blue-400',     'ring' => 'group-hover:ring-blue-200 dark:group-hover:ring-blue-800'],
                    'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-500/10', 'text' => 'text-indigo-600 dark:text-indigo-400', 'ring' => 'group-hover:ring-indigo-200 dark:group-hover:ring-indigo-800'],
                    'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-500/10', 'text' => 'text-purple-600 dark:text-purple-400', 'ring' => 'group-hover:ring-purple-200 dark:group-hover:ring-purple-800'],
                    'green'  => ['bg' => 'bg-emerald-50 dark:bg-emerald-500/10','text' => 'text-emerald-600 dark:text-emerald-400','ring' => 'group-hover:ring-emerald-200 dark:group-hover:ring-emerald-800'],
                    'teal'   => ['bg' => 'bg-teal-50 dark:bg-teal-500/10',     'text' => 'text-teal-600 dark:text-teal-400',     'ring' => 'group-hover:ring-teal-200 dark:group-hover:ring-teal-800'],
                ];
            @endphp

            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Inventory Summary</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Key warehouse & procurement metrics</p>
                </div>
                <a href="{{ route('inventory.stock.index') }}" class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition">
                    View Details →
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-5 gap-y-6">
                @foreach ($miniStats as $m)
                    @php $c = $colorMap[$m['color']] ?? $colorMap['blue']; @endphp
                    <a href="{{ $m['url'] }}" class="flex items-center gap-3.5 group">
                        <div class="w-11 h-11 rounded-xl {{ $c['bg'] }} {{ $c['text'] }} ring-1 ring-transparent {{ $c['ring'] }} flex items-center justify-center shrink-0 group-hover:scale-105 transition-all duration-200">
                            <i data-lucide="{{ $m['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xl font-bold text-gray-900 dark:text-white leading-none tracking-tight">
                                {{ number_format($m['value']) }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 truncate font-medium">
                                {{ $m['label'] }}
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Highlight cards --}}
        <div class="flex flex-col gap-4">
            <a href="{{ route('procurement.index', ['status' => 'pending']) }}"
               class="group relative overflow-hidden flex items-center justify-between rounded-2xl bg-gradient-to-br from-amber-400 to-amber-500 p-5 text-white shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                <div class="pointer-events-none absolute -top-8 -right-8 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative">
                    <div class="text-3xl font-bold leading-none tracking-tight">{{ number_format($stats['pending_request'] ?? 0) }}</div>
                    <div class="text-xs text-amber-50 mt-1.5 font-medium tracking-wide">Pending Request</div>
                </div>
                <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm group-hover:bg-white/25 transition-colors">
                    <i data-lucide="clock" class="w-6 h-6 text-white"></i>
                </div>
            </a>

            <a href="{{ route('procurement.index', ['status' => 'approved']) }}"
               class="group relative overflow-hidden flex items-center justify-between rounded-2xl bg-gradient-to-br from-orange-400 to-orange-500 p-5 text-white shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                <div class="pointer-events-none absolute -top-8 -right-8 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative">
                    <div class="text-3xl font-bold leading-none tracking-tight">{{ number_format($stats['pending_approval'] ?? 0) }}</div>
                    <div class="text-xs text-orange-50 mt-1.5 font-medium tracking-wide">Pending Approval</div>
                </div>
                <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm group-hover:bg-white/25 transition-colors">
                    <i data-lucide="check-square" class="w-6 h-6 text-white"></i>
                </div>
            </a>
        </div>
    </div>

    {{-- ============================================================
         PENDING PER MODUL (BREAKDOWN)
    ============================================================ --}}
    @php
        $moduleStats = [
            [
                'key'      => 'procurement',
                'label'    => 'Procurement Request',
                'pending'  => $moduleTotals['procurement_pending']   ?? 0,
                'approved' => $moduleTotals['procurement_approved']  ?? 0,
                'rejected' => $moduleTotals['procurement_rejected']  ?? 0,
                'total'    => $moduleTotals['procurement_total']     ?? 0,
                'icon'     => 'shopping-bag',
                'color'    => 'blue',
                'url'      => route('procurement.index'),
            ],
            [
                'key'      => 'consumable',
                'label'    => 'Consumable Request',
                'pending'  => $moduleTotals['consumable_pending']    ?? 0,
                'approved' => $moduleTotals['consumable_approved']   ?? 0,
                'rejected' => $moduleTotals['consumable_rejected']   ?? 0,
                'total'    => $moduleTotals['consumable_total']      ?? 0,
                'icon'     => 'clipboard-list',
                'color'    => 'emerald',
                'url'      => auth()->user()->hasRole('Super Admin')
                                ? route('consumable-requests.index')
                                : route('consumable-requests.my-index'),
            ],
            [
                'key'      => 'cash_advance',
                'label'    => 'Cash Advance',
                'pending'  => $moduleTotals['cash_advance_pending']  ?? 0,
                'approved' => $moduleTotals['cash_advance_approved'] ?? 0,
                'rejected' => $moduleTotals['cash_advance_rejected'] ?? 0,
                'total'    => $moduleTotals['cash_advance_total']    ?? 0,
                'icon'     => 'banknote',
                'color'    => 'amber',
                'url'      => '#',
            ],
            [
                'key'      => 'liquidation',
                'label'    => 'Liquidation',
                'pending'  => $moduleTotals['liquidation_pending']   ?? 0,
                'approved' => $moduleTotals['liquidation_approved']  ?? 0,
                'rejected' => $moduleTotals['liquidation_rejected']  ?? 0,
                'total'    => $moduleTotals['liquidation_total']     ?? 0,
                'icon'     => 'receipt',
                'color'    => 'purple',
                'url'      => '#',
            ],
            [
                'key'      => 'reimbursement',
                'label'    => 'Reimbursement',
                'pending'  => $moduleTotals['reimbursement_pending'] ?? 0,
                'approved' => $moduleTotals['reimbursement_approved']?? 0,
                'rejected' => $moduleTotals['reimbursement_rejected']?? 0,
                'total'    => $moduleTotals['reimbursement_total']   ?? 0,
                'icon'     => 'wallet',
                'color'    => 'rose',
                'url'      => '#',
            ],
        ];

        $moduleColorMap = [
            'blue'    => ['iconBg' => 'bg-blue-50 dark:bg-blue-500/10',       'iconText' => 'text-blue-600 dark:text-blue-400',       'accent' => 'bg-blue-500'],
            'emerald' => ['iconBg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'iconText' => 'text-emerald-600 dark:text-emerald-400', 'accent' => 'bg-emerald-500'],
            'amber'   => ['iconBg' => 'bg-amber-50 dark:bg-amber-500/10',     'iconText' => 'text-amber-600 dark:text-amber-400',     'accent' => 'bg-amber-500'],
            'purple'  => ['iconBg' => 'bg-purple-50 dark:bg-purple-500/10',   'iconText' => 'text-purple-600 dark:text-purple-400',   'accent' => 'bg-purple-500'],
            'rose'    => ['iconBg' => 'bg-rose-50 dark:bg-rose-500/10',       'iconText' => 'text-rose-600 dark:text-rose-400',       'accent' => 'bg-rose-500'],
        ];
    @endphp

    <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                    <i data-lucide="layout-grid" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Pending Summary</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Breakdown permintaan per modul</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-gray-100 dark:divide-gray-700/70">
            @foreach ($moduleStats as $mod)
                @php $c = $moduleColorMap[$mod['color']] ?? $moduleColorMap['blue']; @endphp

                <a href="{{ $mod['url'] }}"
                   class="group relative p-5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">

                    {{-- Accent top bar --}}
                    <div class="absolute top-0 left-0 right-0 h-0.5 {{ $c['accent'] }} opacity-0 group-hover:opacity-100 transition-opacity"></div>

                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-lg {{ $c['iconBg'] }} {{ $c['iconText'] }} flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $mod['icon'] }}" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $mod['label'] }}
                                </div>
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ number_format($mod['total']) }} total
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pending (big number) --}}
                    <div class="mb-3">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white leading-none tracking-tight">
                            {{ number_format($mod['pending']) }}
                        </div>
                        <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1.5 font-medium uppercase tracking-wider">
                            Pending
                        </div>
                    </div>

                    {{-- Breakdown approved / rejected --}}
                    <div class="flex items-center gap-3 text-[10px] pt-3 border-t border-gray-100 dark:border-gray-700/70">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            <span class="text-gray-500 dark:text-gray-400">Approved</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-200">
                                {{ number_format($mod['approved']) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-red-500"></span>
                            <span class="text-gray-500 dark:text-gray-400">Rejected</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-200">
                                {{ number_format($mod['rejected']) }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ============================================================
         CHARTS
    ============================================================ --}}
    <div class="grid lg:grid-cols-2 gap-4">
        {{-- Grafik Penggunaan Barang --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Item Usage</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Most frequently used items</p>
                    </div>
                </div>
            </header>

            <div class="p-6">
                @if(isset($usageChartData) && $usageChartData->count() > 0)
                    <div class="h-56 relative">
                        <canvas
                            id="usageChart"
                            data-labels='@json($usageChartData->pluck("item.item_name"))'
                            data-values='@json($usageChartData->pluck("total_used"))'
                        ></canvas>
                    </div>
                @else
                    <div class="h-56 flex flex-col items-center justify-center gap-3 text-gray-400 text-sm border border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                            <i data-lucide="bar-chart-3" class="w-6 h-6 text-gray-400 dark:text-gray-500"></i>
                        </div>
                        <span class="max-w-xs text-center text-xs leading-relaxed">
                            No item usage data yet. Reduce stock to view the chart.
                        </span>
                    </div>
                @endif
            </div>
        </section>

        {{-- Grafik Pembelian --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Purchase Chart</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Monthly procurement trend</p>
                    </div>
                </div>
            </header>

            <div class="p-6">
               <div class="h-56 flex flex-col items-center justify-center gap-3 text-gray-400 text-sm border border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <i data-lucide="trending-up" class="w-6 h-6 text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <span class="max-w-xs text-center text-xs leading-relaxed">
                        Available after the Procurement module (Phase 3) is activated.
                    </span>
                </div>
            </div>
        </section>
    </div>

    {{-- ============================================================
         LOW STOCK + SIDE STATS
    ============================================================ --}}
    <div class="grid lg:grid-cols-3 gap-4">

        {{-- Low stock list --}}
        <section class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Item Low Stock</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Immediate restocking required</p>
                    </div>
                </div>
                <a href="{{ route('inventory.stock.index') }}" class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition">
                    View All →
                </a>
            </header>

            <div class="divide-y divide-gray-100 dark:divide-gray-700/70">
                @forelse ($lowStockItems as $item)
                    <div class="flex items-center justify-between px-6 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center text-xs font-bold shrink-0 ring-1 ring-red-100 dark:ring-red-900/50">
                                {{ strtoupper(substr($item->item_code ?? 'IT', 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                    {{ $item->item_name }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    <span class="font-mono">{{ $item->item_code }}</span>
                                    <span class="mx-1.5 text-gray-300 dark:text-gray-600">·</span>
                                    {{ $item->category->name ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0 pl-3">
                            <div class="inline-flex items-center gap-1 rounded-md bg-red-50 dark:bg-red-500/10 px-2 py-0.5">
                                <span class="text-sm font-semibold text-red-600 dark:text-red-400">{{ $item->current_stock }}</span>
                                <span class="text-xs text-red-500/80 dark:text-red-400/80">{{ $item->unit->code ?? 'Unit' }}</span>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">min. {{ $item->minimum_stock ?? 5 }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-500/10 mb-3">
                            <i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-600 dark:text-emerald-400"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">All stock levels are safe</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">There are no items with low stock at the moment.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Side stats --}}
        <div class="flex flex-col gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Stock</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white mt-2 tracking-tight">
                            {{ number_format($stats['total_stock'] ?? 0) }}
                        </div>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300">
                        <i data-lucide="warehouse" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-500 to-red-600 p-5 text-white shadow-sm">
                <div class="pointer-events-none absolute -top-8 -right-8 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <div class="text-3xl font-bold leading-none tracking-tight">{{ number_format($stats['low_stock'] ?? 0) }}</div>
                        <div class="text-xs text-red-50 mt-2 font-medium tracking-wide">Low Stock Items</div>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-white"></i>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-5 text-white shadow-sm">
                <div class="pointer-events-none absolute -top-8 -right-8 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <div class="text-3xl font-bold leading-none tracking-tight">{{ number_format($stats['goods_received'] ?? 0) }}</div>
                        <div class="text-xs text-emerald-50 mt-2 font-medium tracking-wide">Goods Received</div>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm">
                        <i data-lucide="package-check" class="w-5 h-5 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if(isset($usageChartData) && $usageChartData->count() > 0)
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const canvas = document.getElementById('usageChart');
            const ctx = canvas.getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 220);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.9)');
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.4)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: JSON.parse(canvas.dataset.labels),
                    datasets: [{
                        label: 'Total Dipakai',
                        data: JSON.parse(canvas.dataset.values),
                        backgroundColor: gradient,
                        hoverBackgroundColor: 'rgba(37, 99, 235, 1)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 0,
                        borderRadius: 8,
                        maxBarThickness: 42
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: { size: 12, weight: '600' },
                            bodyFont: { size: 12 },
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: '#94a3b8', font: { size: 11 } },
                            grid: { color: 'rgba(148, 163, 184, 0.1)', drawBorder: false }
                        },
                        x: {
                            ticks: { color: '#94a3b8', font: { size: 11 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
@endif