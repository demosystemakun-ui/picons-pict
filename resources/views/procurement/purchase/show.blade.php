@extends('layouts.app')

@section('title', 'Detail Purchase Order')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-24">
    
    {{-- ═══ ALERT SUCCESS & ERROR ═══ --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl">
            <div class="flex items-center gap-3 mb-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-bold">Terjadi Kesalahan</span>
            </div>
            <ul class="list-disc list-inside text-sm ml-8">
                @if(session('error'))
                    <li>{{ session('error') }}</li>
                @endif
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ═══ INFO PO ═══ --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $purchase->po_number }}</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">
                    PR: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $purchase->procurementRequest->no }}</span> 
                    | Vendor: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $purchase->vendor->vendor_name }}</span>
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                @if($purchase->status === 'ordered') bg-blue-100 text-blue-800 ring-1 ring-blue-200
                @elseif($purchase->status === 'partially_received') bg-amber-100 text-amber-800 ring-1 ring-amber-200
                @else bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200 @endif">
                {{ str_replace('_', ' ', $purchase->status) }}
            </span>
        </div>
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div class="bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                <span class="text-gray-500 block text-xs uppercase tracking-wider mb-1">Tgl Order</span> 
                <span class="font-semibold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($purchase->order_date)->format('d F Y') }}</span>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                <span class="text-gray-500 block text-xs uppercase tracking-wider mb-1">Estimasi Datang</span> 
                <span class="font-semibold text-gray-900 dark:text-white">{{ $purchase->estimated_arrival ? \Carbon\Carbon::parse($purchase->estimated_arrival)->format('d F Y') : '-' }}</span>
            </div>
        </div>
    </div>

    {{-- ═══ FORM PENERIMAAN BARANG ═══ --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-between items-center">
            <h2 class="font-bold text-gray-900 dark:text-white">Form Penerimaan Barang</h2>
            @if($purchase->status === 'completed')
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">Selesai</span>
            @endif
        </div>

        {{-- Kondisi: Jika PO sudah complete, tampilkan tabel read-only --}}
        @if($purchase->status === 'completed')
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/30">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-gray-600 dark:text-gray-400 font-semibold">Nama Barang</th>
                        <th class="px-4 py-3 text-center text-gray-600 dark:text-gray-400 font-semibold">Qty Dipesan</th>
                        <th class="px-4 py-3 text-center text-gray-600 dark:text-gray-400 font-semibold">Total Diterima</th>
                        <th class="px-4 py-3 text-center text-gray-600 dark:text-gray-400 font-semibold">Total Rusak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($purchase->items as $item)
                    <tr>
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $item->product_name }}</td>
                        <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">{{ $item->qty_ordered }}</td>
                        <td class="px-4 py-3 text-center font-bold text-emerald-600">{{ $item->qty_received }}</td>
                        <td class="px-4 py-3 text-center font-bold {{ $item->qty_damaged > 0 ? 'text-rose-600' : 'text-gray-400' }}">{{ $item->qty_damaged }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            {{-- Form Input Aktual --}}
            <form action="{{ route('procurement.purchases.receive', $purchase->id) }}" method="POST">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/30">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-3 text-gray-600 dark:text-gray-400 font-semibold">Nama Barang</th>
                                <th class="px-4 py-3 text-center text-gray-600 dark:text-gray-400 font-semibold">Qty Dipesan</th>
                                <th class="px-4 py-3 text-center text-gray-600 dark:text-gray-400 font-semibold">Sudah Diterima</th>
                                <th class="px-4 py-3 text-center text-teal-600 font-semibold w-32">Qty Datang (Baru)</th>
                                <th class="px-4 py-3 text-center text-rose-600 font-semibold w-32">Qty Rusak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($purchase->items as $item)
                            @php
                                $remaining = $item->qty_ordered - $item->qty_received;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-white">
                                    {{ $item->product_name }}
                                    <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">{{ $item->qty_ordered }}</td>
                                <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $item->qty_received }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($remaining > 0)
                                        <input type="number" 
                                               name="items[{{ $loop->index }}][qty_received]" 
                                               value="{{ $remaining }}" 
                                               min="0" 
                                               max="{{ $remaining }}" 
                                               required
                                               class="w-full text-center rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 focus:border-teal-500 focus:ring-teal-500">
                                    @else
                                        <span class="text-xs text-emerald-600 font-semibold bg-emerald-50 px-2 py-1 rounded">Lunas</span>
                                        <input type="hidden" name="items[{{ $loop->index }}][qty_received]" value="0">
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" 
                                           name="items[{{ $loop->index }}][qty_damaged]" 
                                           value="0" 
                                           min="0" 
                                           max="{{ $remaining }}"
                                           class="w-full text-center rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 focus:border-rose-500 focus:ring-rose-500">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end bg-gray-50/50 dark:bg-gray-900/20">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Konfirmasi Penerimaan
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection