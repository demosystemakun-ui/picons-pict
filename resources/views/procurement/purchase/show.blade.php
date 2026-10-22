@extends('layouts.app')

@section('title', 'Detail Purchase Order')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-24">
    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-800 p-4 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Info PO --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-xl font-bold">{{ $purchase->po_number }}</h1>
                <p class="text-gray-500">PR: {{ $purchase->procurementRequest->no }} | Vendor: {{ $purchase->vendor->vendor_name }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-bold uppercase
                @if($purchase->status === 'ordered') bg-blue-100 text-blue-800
                @elseif($purchase->status === 'partially_received') bg-amber-100 text-amber-800
                @else bg-emerald-100 text-emerald-800 @endif">
                {{ str_replace('_', ' ', $purchase->status) }}
            </span>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500 block">Tgl Order:</span> {{ \Carbon\Carbon::parse($purchase->order_date)->format('d M Y') }}</div>
            <div><span class="text-gray-500 block">Estimasi Datang:</span> {{ $purchase->estimated_arrival ? \Carbon\Carbon::parse($purchase->estimated_arrival)->format('d M Y') : '-' }}</div>
        </div>
    </div>

    {{-- Form Penerimaan --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            <h2 class="font-bold">Form Penerimaan Barang</h2>
        </div>
        <form action="{{ route('procurement.purchases.receive', $purchase->id) }}" method="POST">
            @csrf
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3">Nama Barang</th>
                        <th class="px-4 py-3 text-center">Qty Dipesan</th>
                        <th class="px-4 py-3 text-center">Sudah Diterima</th>
                        <th class="px-4 py-3 text-center text-teal-600">Qty Datang (Baru)</th>
                        <th class="px-4 py-3 text-center text-rose-600">Qty Rusak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($purchase->items as $item)
                    <tr>
                        <td class="px-4 py-3">
                            {{ $item->product_name }}
                            <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                        </td>
                        <td class="px-4 py-3 text-center font-bold">{{ $item->qty_ordered }}</td>
                        <td class="px-4 py-3 text-center">{{ $item->qty_received }}</td>
                        <td class="px-4 py-3 text-center">
                            <input type="number" name="items[{{ $loop->index }}][qty_received]" value="{{ $item->qty_ordered - $item->qty_received }}" min="0" max="{{ $item->qty_ordered - $item->qty_received }}" class="w-20 text-center rounded-lg border-gray-300 dark:bg-gray-900">
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input type="number" name="items[{{ $loop->index }}][qty_damaged]" value="0" min="0" class="w-20 text-center rounded-lg border-gray-300 dark:bg-gray-900">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700">Konfirmasi Penerimaan</button>
            </div>
        </form>
    </div>
</div>
@endsection