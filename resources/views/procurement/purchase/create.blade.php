@extends('layouts.app')

@section('title', 'Buat Purchase Order')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-24">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <h1 class="text-xl font-bold mb-4">Checkout / Buat Purchase Order (PO)</h1>
        
        <form action="{{ route('procurement.purchases.store', $procurement->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Pilih Vendor Pemenang</label>
                <select name="vendor_id" required class="w-full rounded-lg border-gray-300 dark:bg-gray-900">
                    <option value="">-- Pilih Vendor --</option>
                    @foreach($procurement->vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ $vendor->is_winner ? 'selected' : '' }}>
                            {{ $vendor->vendor_name }} - Rp {{ number_format($vendor->total_price, 0, ',', '.') }} {{ $vendor->is_winner ? '(Termurah)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nomor PO</label>
                    <input type="text" name="po_number" value="PO-{{ date('Ymd') }}-{{ $procurement->id }}" required class="w-full rounded-lg border-gray-300 dark:bg-gray-900">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Tanggal Order</label>
                    <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border-gray-300 dark:bg-gray-900">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Estimasi Barang Datang</label>
                <input type="date" name="estimated_arrival" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full rounded-lg border-gray-300 dark:bg-gray-900">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Catatan</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 dark:bg-gray-900"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <a href="{{ route('procurement.comparisons.index', $procurement->id) }}" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</a>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg">Proses Checkout (PO)</button>
            </div>
        </form>
    </div>
</div>
@endsection