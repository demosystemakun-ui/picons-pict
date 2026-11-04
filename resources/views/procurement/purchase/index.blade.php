@extends('layouts.app')

@section('title', 'Daftar Purchase Order')
@section('page-title', 'Purchase Orders (PO)')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Daftar Purchase Order</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pantau status pemesanan barang dan penerimaannya.</p>
        </div>
    </div>

    {{-- FORM PENCARIAN & FILTER --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <form action="{{ route('procurement.purchases.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. PO atau Vendor..." 
                   class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm">
            
            <select name="status" class="w-full sm:w-48 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm">
                <option value="">Semua Status</option>
                <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Ordered</option>
                <option value="partially_received" {{ request('status') == 'partially_received' ? 'selected' : '' }}>Partially Received</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>

            <button type="submit" class="inline-flex justify-center items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Filter
            </button>
            
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('procurement.purchases.index') }}" class="inline-flex justify-center items-center rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- TABEL DAFTAR PO --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">No. PO / Tanggal</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Vendor</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ref. PR</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estimasi Datang</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($purchases as $po)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                        <td class="px-4 py-4">
                            <div class="font-mono font-bold text-gray-900 dark:text-white">{{ $po->po_number }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($po->order_date)->format('d M Y') }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $po->vendor->vendor_name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $po->vendor->marketplace ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <a href="{{ route('procurement.show', $po->procurement_request_id) }}" class="text-blue-600 hover:underline font-medium">
                                {{ $po->procurementRequest->no ?? '-' }}
                            </a>
                        </td>
                        <td class="px-4 py-4 text-gray-700 dark:text-gray-300">
                            {{ $po->estimated_arrival ? \Carbon\Carbon::parse($po->estimated_arrival)->format('d M Y') : '-' }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($po->status === 'ordered')
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-600/20">Ordered</span>
                            @elseif($po->status === 'partially_received')
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">Partial</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Completed</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('procurement.purchases.show', $po->id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                            <p class="font-medium text-gray-900 dark:text-white">Belum ada Purchase Order</p>
                            <p class="text-xs mt-1">PO akan muncul di sini setelah dibuat dari menu Procurement Request.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($purchases->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>
</div>
@endsection