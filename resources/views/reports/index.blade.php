@extends('layouts.app')

@section('title', 'Laporan Pembelian')
@section('page-title', 'Laporan & Analitik')

@section('content')
<div class="space-y-6 pb-12">
    
    {{-- FILTER TANGGAL --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col sm:flex-row items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                Tampilkan Laporan
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- ═══ BARANG PALING SERING DIBELI ═══ --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                <h2 class="font-semibold text-gray-800">Barang Paling Sering Dibeli</h2>
                <p class="text-xs text-gray-500">Berdasarkan kuantitas pembelian (Qty) dari Purchase Order</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3">Nama Barang</th>
                            <th class="px-5 py-3 text-center">Total Qty</th>
                            <th class="px-5 py-3 text-right">Total Pengeluaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topItems as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $item->product_name }}</td>
                                <td class="px-5 py-3 text-center font-bold text-blue-600">{{ number_format($item->total_qty) }}</td>
                                <td class="px-5 py-3 text-right text-gray-600">Rp {{ number_format($item->total_spent, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400">Tidak ada data pembelian di periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══ VENDOR PALING AKTIF ═══ --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                <h2 class="font-semibold text-gray-800">Vendor Paling Aktif</h2>
                <p class="text-xs text-gray-500">Berdasarkan total belanja dan jumlah PO</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3">Nama Vendor</th>
                            <th class="px-5 py-3 text-center">Total PO</th>
                            <th class="px-5 py-3 text-right">Total Belanja</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topVendors as $vendor)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $vendor->vendor_name }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="bg-indigo-50 text-indigo-700 font-semibold px-2.5 py-1 rounded-md text-xs">{{ $vendor->total_po }} PO</span>
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600">Rp {{ number_format($vendor->total_spent, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400">Tidak ada data vendor di periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection