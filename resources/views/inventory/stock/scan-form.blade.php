@extends('layouts.app')

@section('title', 'Ambil Barang')
@section('content')
<div class="max-w-md mx-auto mt-10 p-4">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        
        <div class="bg-blue-600 p-6 text-white text-center">
            <h1 class="text-xl font-bold">Ambil Barang</h1>
            <p class="text-sm text-blue-200 mt-1">Silakan konfirmasi pemakaian barang di bawah ini.</p>
        </div>

        <div class="p-6">
            <div class="mb-6 text-center">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Nama Barang</p>
                <h2 class="text-2xl font-bold text-gray-800">{{ $stock->item->item_name }}</h2>
                <p class="text-sm font-mono text-gray-500">{{ $stock->item->item_code }}</p>
                
                <div class="mt-3 inline-block bg-gray-100 px-4 py-1.5 rounded-full">
                    <span class="text-sm text-gray-600">Sisa Stok: </span>
                    <span class="font-bold text-blue-600">{{ $stock->stock }}</span>
                </div>
            </div>

            @if(session('error'))
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            <form action="{{ route('inventory.scan.submit', $stock->id) }}" method="POST">
                @csrf
                
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah yang Diambil</label>
                    <input type="number" name="quantity" value="1" min="1" max="{{ $stock->stock }}" required
                           class="w-full text-center text-2xl font-bold h-14 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-0">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keperluan / Tujuan</label>
                    <textarea name="purpose" rows="2" required placeholder="Contoh: Untuk cetak laporan bulanan"
                              class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-0 text-sm"></textarea>
                </div>

                <div class="mb-6 bg-blue-50 p-3 rounded-lg flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-200 rounded-full flex items-center justify-center">
                        <i data-lucide="user" class="w-5 h-5 text-blue-700"></i>
                    </div>
                    <div>
                        <p class="text-xs text-blue-600">Diambil oleh:</p>
                        <p class="text-sm font-bold text-blue-900">{{ auth()->user()->name }}</p>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg h-12 rounded-xl transition shadow-lg">
                    Konfirmasi Ambil
                </button>
            </form>
        </div>
    </div>
</div>
@endsection