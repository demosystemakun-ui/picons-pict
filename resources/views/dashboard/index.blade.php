{{--
    Butuh palet berikut di layouts/app.blade.php (setelah script Tailwind CDN), jika belum ada:

    tailwind.config = { darkMode: 'class', theme: { extend: { colors: {
        navy: { 50:'#f3f6fa', 100:'#e1e9f3', 200:'#c3d3e7', 300:'#97b2d3', 600:'#0E4C92', 700:'#0B3A73', 800:'#0A2A52', 900:'#07192F' }
    } } } }
--}}
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $panel = 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg';
    $head  = 'flex items-center justify-between px-5 py-3.5 border-b border-gray-200 dark:border-gray-700';
    $title = 'text-sm font-semibold text-gray-900 dark:text-white';
    $link  = 'text-xs font-medium text-navy-600 dark:text-navy-300 hover:underline underline-offset-2';

    $kpis = [
        ['label' => 'Total Item',       'value' => $stats['total_item']       ?? 0, 'url' => route('items.index')],
        ['label' => 'Total Stock',      'value' => $stats['total_stock']      ?? 0, 'url' => route('inventory.stock.index')],
        ['label' => 'Purchase Order',   'value' => $stats['purchase_order']   ?? 0, 'url' => '#'],
        ['label' => 'Goods Received',   'value' => $stats['goods_received']   ?? 0, 'url' => '#'],
        ['label' => 'Pending Request',  'value' => $stats['pending_request']  ?? 0, 'url' => route('procurement.index', ['status' => 'pending'])],
        ['label' => 'Pending Approval', 'value' => $stats['pending_approval'] ?? 0, 'url' => route('procurement.index', ['status' => 'approved'])],
    ];
    if (auth()->check() && auth()->user()->hasRole('Super Admin')) {
        $kpis[] = ['label' => 'Total Vendor', 'value' => $stats['total_vendor'] ?? 0, 'url' => route('vendors.index')];
    }

    $modules = [
        ['key' => 'procurement',   'label' => 'Procurement Request', 'url' => route('procurement.index')],
        ['key' => 'consumable',    'label' => 'Consumable Request',  'url' => auth()->user()->hasRole('Super Admin') ? route('consumable-requests.index') : route('consumable-requests.my-index')],
        ['key' => 'cash_advance',  'label' => 'Cash Advance',        'url' => '#'],
        ['key' => 'liquidation',   'label' => 'Liquidation',         'url' => '#'],
        ['key' => 'reimbursement', 'label' => 'Reimbursement',       'url' => '#'],
    ];
@endphp

<div class="space-y-6 pb-12">

    {{-- Header halaman --}}
    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Stock, requests, and procurement activity at a glance.</p>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400 tabular-nums">{{ now()->format('l, d F Y') }}</p>
    </div>

    {{-- KPI --}}
    <div class="{{ $panel }} overflow-hidden">
        <div class="grid gap-px bg-gray-200 dark:bg-gray-700" style="grid-template-columns: repeat(auto-fit, minmax(9.5rem, 1fr))">
            @foreach ($kpis as $k)
                <a href="{{ $k['url'] }}" class="block bg-white dark:bg-gray-800 px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $k['label'] }}</div>
                    <div class="mt-1.5 text-2xl font-semibold tabular-nums text-gray-900 dark:text-white">{{ number_format($k['value']) }}</div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Ringkasan per modul --}}
    <section class="{{ $panel }} overflow-hidden">
        <header class="{{ $head }}">
            <h2 class="{{ $title }}">Requests by module</h2>
            <div class="hidden sm:flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm bg-emerald-600"></span>Approved</span>
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm bg-amber-500"></span>Pending</span>
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm bg-red-600"></span>Rejected</span>
            </div>
        </header>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-5 py-2.5 font-medium">Module</th>
                        <th class="px-3 py-2.5 font-medium text-right">Total</th>
                        <th class="px-3 py-2.5 font-medium text-right">Pending</th>
                        <th class="px-3 py-2.5 font-medium text-right">Approved</th>
                        <th class="px-3 py-2.5 font-medium text-right">Rejected</th>
                        <th class="px-5 py-2.5 font-medium w-48 hidden md:table-cell">Distribution</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70">
                    @foreach ($modules as $m)
                        @php
                            $p = $moduleTotals[$m['key'].'_pending']  ?? 0;
                            $a = $moduleTotals[$m['key'].'_approved'] ?? 0;
                            $r = $moduleTotals[$m['key'].'_rejected'] ?? 0;
                            $t = $moduleTotals[$m['key'].'_total']    ?? 0;
                            $d = max($t, 1);
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-5 py-3">
                                <a href="{{ $m['url'] }}" class="font-medium text-gray-900 dark:text-white hover:text-navy-600 dark:hover:text-navy-300">{{ $m['label'] }}</a>
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums text-gray-600 dark:text-gray-300">{{ number_format($t) }}</td>
                            <td class="px-3 py-3 text-right tabular-nums font-semibold text-gray-900 dark:text-white">{{ number_format($p) }}</td>
                            <td class="px-3 py-3 text-right tabular-nums text-gray-600 dark:text-gray-300">{{ number_format($a) }}</td>
                            <td class="px-3 py-3 text-right tabular-nums text-gray-600 dark:text-gray-300">{{ number_format($r) }}</td>
                            <td class="px-5 py-3 hidden md:table-cell">
                                <div class="flex h-1.5 w-full overflow-hidden rounded-sm bg-gray-100 dark:bg-gray-700" role="img"
                                     aria-label="Approved {{ $a }}, pending {{ $p }}, rejected {{ $r }}">
                                    @if ($t > 0)
                                        <span class="bg-emerald-600" style="width: {{ $a / $d * 100 }}%"></span>
                                        <span class="bg-amber-500"   style="width: {{ $p / $d * 100 }}%"></span>
                                        <span class="bg-red-600"     style="width: {{ $r / $d * 100 }}%"></span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- Grafik + stok menipis --}}
    <div class="grid gap-6 lg:grid-cols-5">

        <section class="{{ $panel }} lg:col-span-3 overflow-hidden">
            <header class="{{ $head }}">
                <h2 class="{{ $title }}">Item usage</h2>
                <span class="text-xs text-gray-500 dark:text-gray-400">Most frequently used</span>
            </header>
            <div class="p-5">
                @if(isset($usageChartData) && $usageChartData->count() > 0)
                    <div class="relative h-64">
                        <canvas id="usageChart"
                                data-labels='@json($usageChartData->pluck("item.item_name"))'
                                data-values='@json($usageChartData->pluck("total_used"))'></canvas>
                    </div>
                @else
                    <p class="flex h-64 items-center justify-center text-sm text-gray-500 dark:text-gray-400">
                        No usage recorded yet. Reduce stock to see the chart.
                    </p>
                @endif
            </div>
        </section>

        <section class="{{ $panel }} lg:col-span-2 overflow-hidden">
            <header class="{{ $head }}">
                <h2 class="{{ $title }}">
                    Low stock
                    @if (($stats['low_stock'] ?? 0) > 0)
                        <span class="ml-1.5 font-semibold tabular-nums text-red-600 dark:text-red-400">{{ number_format($stats['low_stock']) }}</span>
                    @endif
                </h2>
                <a href="{{ route('inventory.stock.index') }}" class="{{ $link }}">View all</a>
            </header>

            <ul class="divide-y divide-gray-100 dark:divide-gray-700/70">
                @forelse ($lowStockItems as $item)
                    <li class="flex items-center justify-between gap-4 px-5 py-3">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $item->item_name }}</div>
                            <div class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                                <span class="font-mono">{{ $item->item_code }}</span>
                                <span class="mx-1">/</span>{{ $item->category->name ?? '-' }}
                            </div>
                        </div>
                        <div class="shrink-0 text-right tabular-nums">
                            <div class="text-sm font-semibold text-red-600 dark:text-red-400">
                                {{ $item->current_stock }} <span class="font-normal text-xs">{{ $item->unit->code ?? 'Unit' }}</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">min {{ $item->minimum_stock ?? 5 }}</div>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                        All stock levels are above minimum.
                    </li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
@endsection

@if(isset($usageChartData) && $usageChartData->count() > 0)
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const canvas = document.getElementById('usageChart');
            const dark = document.documentElement.classList.contains('dark');
            const tick = dark ? '#9ca3af' : '#6b7280';

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: JSON.parse(canvas.dataset.labels),
                    datasets: [{
                        label: 'Total Used',
                        data: JSON.parse(canvas.dataset.values),
                        backgroundColor: '#0E4C92',
                        hoverBackgroundColor: '#0B3A73',
                        borderRadius: 2,
                        maxBarThickness: 32
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: '#07192F', padding: 10, cornerRadius: 4, displayColors: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            ticks: { stepSize: 1, color: tick, font: { size: 11 } },
                            grid: { color: dark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.06)' }
                        },
                        x: {
                            border: { color: dark ? 'rgba(255,255,255,.15)' : 'rgba(0,0,0,.15)' },
                            ticks: { color: tick, font: { size: 11 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
@endif