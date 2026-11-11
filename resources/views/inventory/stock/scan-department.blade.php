<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Katalog Barang - {{ $department->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Mencegah user melakukan zoom yang tidak diinginkan di HP */
        * { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    {{-- HEADER FULL SCREEN --}}
    <header class="bg-blue-600 px-4 py-5 text-white shadow-md sticky top-0 z-40">
        <div class="max-w-md mx-auto">
            <p class="text-blue-200 text-xs mb-1">Lemari / Departemen:</p>
            <h1 class="text-xl font-bold">{{ $department->name }}</h1>
            <p class="text-xs mt-1 text-blue-100">Pilih barang yang ingin Anda ambil.</p>
        </div>
    </header>

    {{-- KONTEN UTAMA --}}
    <main class="max-w-md mx-auto p-4 space-y-4 pb-24">
        
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
        @forelse($stocks as $stock)
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm flex items-center justify-between gap-4">
                
                {{-- Info Barang --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center shrink-0">
                        <i data-lucide="package" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm leading-tight">{{ $stock->item->item_name }}</h3>
                        <p class="text-[10px] text-gray-500 font-mono mt-0.5">{{ $stock->item->item_code }}</p>
                        <p class="text-xs text-gray-600 mt-1">Sisa: <span class="font-bold {{ $stock->stock > 5 ? 'text-emerald-600' : 'text-red-500' }}">{{ $stock->stock }}</span></p>
                    </div>
                </div>

                {{-- Tombol Ambil --}}
                <button type="button" onclick="openTakeModal({{ $stock->id }}, '{{ addslashes($stock->item->item_name) }}', {{ $stock->stock }})" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition shrink-0">
                    Ambil
                </button>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4 text-gray-400">
                    <i data-lucide="inbox" class="w-8 h-8"></i>
                </div>
                <h3 class="text-base font-medium text-gray-800">Stok Kosong</h3>
                <p class="text-gray-500 text-sm mt-1">Belum ada barang yang tersedia di lemari ini.</p>
            </div>
        @endforelse
    </main>

    {{-- TOMBOL KELUAR / SELESAI (Floating di bawah) --}}
    <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/90 backdrop-blur border-t border-gray-200">
        <div class="max-w-md mx-auto">
            <a href="{{ route('dashboard') }}" class="block w-full text-center py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl transition text-sm">
                Selesai & Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- ═══ MODAL FORM AMBIL BARANG ═══ --}}
    <div id="takeModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeTakeModal()"></div>
        <div class="relative z-10 flex items-center justify-center min-h-full p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-5 shadow-2xl">
                
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900" id="modalItemName">Nama Barang</h3>
                        <p class="text-xs text-gray-500">Maksimal: <span id="modalMaxStock">0</span> unit</p>
                    </div>
                    <button type="button" onclick="closeTakeModal()" class="text-gray-400 hover:text-gray-600 bg-gray-100 rounded-full p-1.5">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form id="takeForm" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah</label>
                        <input type="number" name="used_stock" id="modalQtyInput" value="1" min="1" required
                               class="w-full text-center text-2xl font-bold h-14 rounded-xl border-2 border-blue-500 bg-blue-50 text-blue-700 focus:ring-0 focus:outline-none">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Keperluan <span class="text-red-500">*</span></label>
                        <textarea name="purpose" rows="2" required placeholder="Contoh: Untuk cetak dokumen"
                                  class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-0 text-sm p-3 resize-none bg-gray-50"></textarea>
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
        // Inisialisasi Lucide Icon
        lucide.createIcons();

        function openTakeModal(id, name, maxStock) {
            const modal = document.getElementById('takeModal');
            const form = document.getElementById('takeForm');
            
            form.action = `/inventory/scan-item/${id}`; 
            
            document.getElementById('modalItemName').textContent = name;
            document.getElementById('modalMaxStock').textContent = maxStock;
            
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
</body>
</html>