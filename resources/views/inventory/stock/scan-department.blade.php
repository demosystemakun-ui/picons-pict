@extends('layouts.app')

@section('title', 'Katalog Barang')
@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-24 pt-6 px-4">

    {{-- HEADER DEPARTEMEN --}}
    <div class="bg-blue-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10">
            <p class="text-blue-200 text-sm mb-1">Anda berada di lemari:</p>
            <h1 class="text-2xl font-bold">{{ $department->name }}</h1>
            <p class="text-sm mt-2 opacity-90">Silakan pilih barang yang ingin Anda ambil di bawah ini.</p>
        </div>
        <i data-lucide="box" class="absolute -right-4 -bottom-4 w-32 h-32 text-blue-500/30"></i>
    </div>

    {{-- ALERT SUCCESS/ERROR --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-200 flex items-center gap-2 shadow-sm">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-200 flex items-center gap-2 shadow-sm">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- DAFTAR BARANG --}}
    <div class="grid grid-cols-1 gap-4">
        @forelse($stocks as $stock)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                
                {{-- Info Barang --}}
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 rounded-lg flex items-center justify-center shrink-0">
                        <i data-lucide="package" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-base leading-tight">{{ $stock->item->item_name }}</h3>
                        <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $stock->item->item_code }}</p>
                        <div class="mt-1">
                            <span class="text-xs text-gray-600 dark:text-gray-400">Sisa Stok: </span>
                            <span class="text-sm font-bold {{ $stock->stock > 5 ? 'text-emerald-600' : 'text-red-500' }}">{{ $stock->stock }}</span>
                        </div>
                    </div>
                </div>

                {{-- Tombol Ambil (Buka Modal) --}}
                <button type="button" onclick="openTakeModal({{ $stock->id }}, '{{ addslashes($stock->item->item_name) }}', {{ $stock->stock }})" 
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    Ambil Barang
                </button>
            </div>
        @empty
            <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-700 mb-4">
                    <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Stok Kosong</h3>
                <p class="text-gray-500 text-sm mt-1">Belum ada barang yang tersedia di lemari ini.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- ═══ MODAL FORM AMBIL BARANG ═══ --}}
<div id="takeModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeTakeModal()"></div>
    <div class="relative z-10 flex items-center justify-center min-h-full p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700">
            
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="modalItemName">Nama Barang</h3>
                    <p class="text-sm text-gray-500">Maksimal: <span id="modalMaxStock">0</span> unit</p>
                </div>
                <button type="button" onclick="closeTakeModal()" class="text-gray-400 hover:text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-full p-1.5 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Form mengarah ke route 'inventory.scan.item' --}}
            <form id="takeForm" method="POST">
                @csrf
                
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Jumlah yang Diambil</label>
                    <input type="number" name="used_stock" id="modalQtyInput" value="1" min="1" required
                           class="w-full text-center text-2xl font-bold h-14 rounded-xl border-2 border-blue-500 bg-blue-50 text-blue-700 focus:ring-0 focus:outline-none">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Keperluan <span class="text-red-500">*</span></label>
                    <textarea name="purpose" rows="2" required placeholder="Contoh: Untuk cetak laporan"
                              class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 focus:border-blue-500 focus:ring-0 text-sm p-3 resize-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-base h-12 rounded-xl transition shadow-md">
                    Konfirmasi Ambil
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openTakeModal(id, name, maxStock) {
        const modal = document.getElementById('takeModal');
        const form = document.getElementById('takeForm');
        
        // Set action URL secara dinamis berdasarkan ID barang
        form.action = `/inventory/scan-item/${id}`; 
        
        // Set text
        document.getElementById('modalItemName').textContent = name;
        document.getElementById('modalMaxStock').textContent = maxStock;
        
        // Set max input
        const qtyInput = document.getElementById('modalQtyInput');
        qtyInput.max = maxStock;
        qtyInput.value = 1;

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeTakeModal() {
        const modal = document.getElementById('takeModal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
</script>
@endpush
@endsection