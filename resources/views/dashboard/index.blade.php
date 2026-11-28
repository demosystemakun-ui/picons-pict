{{--
    PALET: tambahkan sekali di layouts/app.blade.php (setelah script Tailwind CDN), jika belum ada:

    tailwind.config = { darkMode: 'class', theme: { extend: { colors: {
        navy:   { 50:'#f3f6fa', 100:'#e1e9f3', 200:'#c3d3e7', 300:'#97b2d3', 400:'#5f8bbd', 500:'#2d6aae', 600:'#0E4C92', 700:'#0B3A73', 800:'#0A2A52', 900:'#07192F' },
        signal: { 500:'#D71920', 600:'#B8141A' }
    } } } }
--}}
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $card   = 'bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm';
    $header = 'px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/50 flex items-center justify-between';
    $chip   = 'flex items-center justify-center shrink-0 bg-navy-50 dark:bg-navy-600/20 text-navy-600 dark:text-navy-300';
    $link   = 'text-xs font-medium text-navy-600 dark:text-navy-300 hover:text-navy-800 dark:hover:text-white transition';
@endphp
<div class="space-y-6 pb-12">

    {{-- ===== HERO ===== --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-navy-900 via-navy-800 to-navy-600 px-6 py-8 text-white shadow-lg ring-1 ring-white/5">
        <svg class="pointer-events-none absolute inset-x-0 bottom-0 h-24 w-full text-white opacity-[0.06]" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
            <path fill="currentColor" d="M0 70 C150 20 300 120 450 70 C600 20 750 120 900 70 C1050 20 1125 60 1200 80 V120 H0Z"/>
        </svg>
        <div class="pointer-events-none absolute -top-24 -right-24 h-64 w-64 rounded-full bg-sky-400/15 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-16 h-64 w-64 rounded-full bg-blue-500/15 blur-3xl"></div>
        <div class="absolute left-0 top-0 h-full w-1 bg-signal-500"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-medium text-white/90 ring-1 ring-white/15">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    Patimban Port · Consumable Management
                </span>
                <h1 class="mt-3 text-2xl sm:text-3xl font-bold tracking-tight">Dashboard</h1>
                <p class="mt-1.5 text-sm text-slate-300 max-w-lg">
                    Real-time overview of the company's stock, requests, and procurement activities.
                </p>
            </div>
            <div class="flex items-center gap-1.5 text-sm text-slate-300">
                <i data-lucide="home" class="w-4 h-4"></i>
                <span>/</span>
                <span class="text-white font-medium">Dashboard</span>
            </div>
        </div>
    </div>

    {{-- ===== STAT CARDS ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Inventory Summary --}}
        <div class="lg:col-span-2 {{ $card }} p-6">
            @php
                $miniStats = [
                    ['label' => 'Total Item',     'value' => $stats['total_item']     ?? 0, 'icon' => 'package',       'url' => route('items.index')],
                    ['label' => 'Total Stock',    'value' => $stats['total_stock']    ?? 0, 'icon' => 'boxes',         'url' => route('inventory.stock.index')],
                    ['label' => 'Purchase Order', 'value' => $stats['purchase_order'] ?? 0, 'icon' => 'shopping-cart', 'url' => '#'],
                    ['label' => 'Goods Received', 'value' => $stats['goods_received'] ?? 0, 'icon' => 'truck',         'url' => '#'],
                ];

                if (auth()->check() && auth()->user()->hasRole('Super Admin')) {
                    $miniStats[] = ['label' => 'Total Vendor', 'value' => $stats['total_vendor'] ?? 0, 'icon' => 'building-2', 'url' => route('vendors.index')];
                }
            @endphp

            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Inventory Summary</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Key warehouse &amp; procurement metrics</p>
                </div>
                <a href="{{ route('inventory.stock.index') }}" class="{{ $link }}">View details →</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-5 gap-y-6">
                @foreach ($miniStats as $m)
                    <a href="{{ $m['url'] }}" class="flex items-center gap-3.5 group">
                        <div class="w-11 h-11 rounded-xl {{ $chip }} ring-1 ring-transparent group-hover:ring-navy-200 dark:group-hover:ring-navy-600 group-hover:scale-105 transition-all duration-200">
                            <i data-lucide="{{ $m['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xl font-bold text-gray-900 dark:text-white leading-none tracking-tight">{{ number_format($m['value']) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 truncate font-medium">{{ $m['label'] }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Highlight cards --}}
        <div class="flex flex-col gap-4">
            <a href="{{ route('procurement.index', ['status' => 'pending']) }}"
               class="group flex flex-1 items-center justify-between {{ $card }} border-l-4 !border-l-amber-500 p-5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                <div>
                    <div class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white">{{ number_format($stats['pending_request'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 font-medium">Pending Request</div>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
            </a>

            <a href="{{ route('procurement.index', ['status' => 'approved']) }}"
               class="group flex flex-1 items-center justify-between {{ $card }} border-l-4 !border-l-navy-600 p-5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                <div>
                    <div class="text-3xl font-bold leading-none tracking-tight text-gray-900 dark:text-white">{{ number_format($stats['pending_approval'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 font-medium">Pending Approval</div>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $chip }}">
                    <i data-lucide="check-square" class="w-6 h-6"></i>
                </div>
            </a>
        </div>
    </div>

    {{-- ===== PENDING SUMMARY PER MODULE ===== --}}
    @php
        $moduleStats = [
            ['key' => 'procurement',   'label' => 'Procurement Request', 'icon' => 'shopping-bag',   'url' => route('procurement.index')],
            ['key' => 'consumable',    'label' => 'Consumable Request',  'icon' => 'clipboard-list', 'url' => auth()->user()->hasRole('Super Admin') ? route('consumable-requests.index') : route('consumable-requests.my-index')],
            ['key' => 'cash_advance',  'label' => 'Cash Advance',        'icon' => 'banknote',       'url' => '#'],
            ['key' => 'liquidation',   'label' => 'Liquidation',         'icon' => 'receipt',        'url' => '#'],
            ['key' => 'reimbursement', 'label' => 'Reimbursement',       'icon' => 'wallet',         'url' => '#'],
        ];
        foreach ($moduleStats as &$mod) {
            $k = $mod['key'];
            $mod['pending']  = $moduleTotals[$k.'_pending']  ?? 0;
            $mod['approved'] = $moduleTotals[$k.'_approved'] ?? 0;
            $mod['rejected'] = $moduleTotals[$k.'_rejected'] ?? 0;
            $mod['total']    = $moduleTotals[$k.'_total']    ?? 0;
        }
        unset($mod);
    @endphp

    <section class="{{ $card }} overflow-hidden">
        <header class="{{ $header }}">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-lg {{ $chip }}"><i data-lucide="layout-grid" class="w-5 h-5"></i></div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Pending Summary</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Requests breakdown by module</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-gray-100 dark:divide-gray-700/70">
            @foreach ($moduleStats as $mod)
                @php
                    $t = max((int) $mod['total'], 1);
                    $pa = round($mod['approved'] / $t * 100);
                    $pp = round($mod['pending']  / $t * 100);
                    $pr = round($mod['rejected'] / $t * 100);
                @endphp
                <a href="{{ $mod['url'] }}" class="group relative p-5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <div class="absolute top-0 left-0 right-0 h-0.5 bg-signal-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                    <div class="flex items-center gap-2.5 min-w-0 mb-4">
                        <div class="w-9 h-9 rounded-lg {{ $chip }}"><i data-lucide="{{ $mod['icon'] }}" class="w-4 h-4"></i></div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-gray-900 dark:text-white truncate">{{ $mod['label'] }}</div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">{{ number_format($mod['total']) }} total</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white leading-none tracking-tight">{{ number_format($mod['pending']) }}</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1.5 font-medium">Pending</div>
                    </div>

                    {{-- Distribusi status --}}
                    <div class="flex h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700" role="img"
                         aria-label="Approved {{ $mod['approved'] }}, pending {{ $mod['pending'] }}, rejected {{ $mod['rejected'] }}">
                        @if ($mod['total'] > 0)
                            <span class="bg-emerald-500" style="width: {{ $pa }}%"></span>
                            <span class="bg-amber-400"   style="width: {{ $pp }}%"></span>
                            <span class="bg-red-500"     style="width: {{ $pr }}%"></span>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 text-[11px] pt-3">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            <span class="text-gray-500 dark:text-gray-400">Approved</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ number_format($mod['approved']) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-red-500"></span>
                            <span class="text-gray-500 dark:text-gray-400">Rejected</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ number_format($mod['rejected']) }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ===== CHARTS ===== --}}
    <div class="grid lg:grid-cols-2 gap-4">
        <section class="{{ $card }} overflow-hidden">
            <header class="{{ $header }}">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-lg {{ $chip }}"><i data-lucide="bar-chart-3" class="w-5 h-5"></i></div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Item Usage</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Most frequently used items</p>
                    </div>
                </div>
            </header>
            <div class="p-6">
                @if(isset($usageChartData) && $usageChartData->count() > 0)
                    <div class="h-56 relative">
                        <canvas id="usageChart"
                                data-labels='@json($usageChartData->pluck("item.item_name"))'
                                data-values='@json($usageChartData->pluck("total_used"))'></canvas>
                    </div>
                @else
                    <div class="h-56 flex flex-col items-center justify-center gap-3 text-gray-400 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                            <i data-lucide="bar-chart-3" class="w-6 h-6 text-gray-400 dark:text-gray-500"></i>
                        </div>
                        <span class="max-w-xs text-center text-xs leading-relaxed">No item usage data yet. Reduce stock to view the chart.</span>
                    </div>
                @endif
            </div>
        </section>

        <section class="{{ $card }} overflow-hidden">
            <header class="{{ $header }}">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-lg {{ $chip }}"><i data-lucide="trending-up" class="w-5 h-5"></i></div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Purchase Chart</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Monthly procurement trend</p>
                    </div>
                </div>
            </header>
            <div class="p-6">
                <div class="h-56 flex flex-col items-center justify-center gap-3 text-gray-400 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <i data-lucide="trending-up" class="w-6 h-6 text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <span class="max-w-xs text-center text-xs leading-relaxed">Available after the Procurement module (Phase 3) is activated.</span>
                </div>
            </div>
        </section>
    </div>

    {{-- ===== LOW STOCK + SIDE STATS ===== --}}
    <div class="grid lg:grid-cols-3 gap-4">

        <section class="lg:col-span-2 {{ $card }} overflow-hidden">
            <header class="{{ $header }}">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Item Low Stock</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Immediate restocking required</p>
                    </div>
                </div>
                <a href="{{ route('inventory.stock.index') }}" class="{{ $link }}">View all →</a>
            </header>

            <div class="divide-y divide-gray-100 dark:divide-gray-700/70">
                @forelse ($lowStockItems as $item)
                    <div class="flex items-center justify-between px-6 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center text-xs font-bold shrink-0 ring-1 ring-red-100 dark:ring-red-900/50">
                                {{ strtoupper(substr($item->item_code ?? 'IT', 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-navy-600 dark:group-hover:text-navy-300 transition-colors">{{ $item->item_name }}</div>
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

        <div class="flex flex-col gap-4">
            <div class="{{ $card }} border-l-4 !border-l-navy-600 p-5 flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Stock</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-2 tracking-tight">{{ number_format($stats['total_stock'] ?? 0) }}</div>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $chip }}"><i data-lucide="warehouse" class="w-5 h-5"></i></div>
            </div>

            <div class="{{ $card }} border-l-4 !border-l-signal-500 p-5 flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Low Stock Items</div>
                    <div class="text-2xl font-bold text-signal-600 dark:text-red-400 mt-2 tracking-tight">{{ number_format($stats['low_stock'] ?? 0) }}</div>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400"><i data-lucide="alert-triangle" class="w-5 h-5"></i></div>
            </div>

            <div class="{{ $card }} border-l-4 !border-l-emerald-500 p-5 flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Goods Received</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-2 tracking-tight">{{ number_format($stats['goods_received'] ?? 0) }}</div>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"><i data-lucide="package-check" class="w-5 h-5"></i></div>
            </div>
        </div>
    </div>
</div>
@endsection

@if(isset($usageChartData) && $usageChartData->count() > 0)
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const canvas = document.getElementById('usageChart');
            const ctx = canvas.getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 220);
            gradient.addColorStop(0, 'rgba(14, 76, 146, 0.95)');
            gradient.addColorStop(1, 'rgba(14, 76, 146, 0.45)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: JSON.parse(canvas.dataset.labels),
                    datasets: [{
                        label: 'Total Used',
                        data: JSON.parse(canvas.dataset.values),
                        backgroundColor: gradient,
                        hoverBackgroundColor: 'rgba(215, 25, 32, 0.95)',
                        borderWidth: 0,
                        borderRadius: 6,
                        maxBarThickness: 38
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(7, 25, 47, 0.96)',
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
                            grid: { color: 'rgba(148, 163, 184, 0.12)', drawBorder: false }
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