<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ambil Barang - {{ $stock->item->item_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col justify-center p-4">

    <div class="max-w-md w-full mx-auto">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            
            {{-- HEADER --}}
            <div class="bg-blue-600 p-6 text-white text-center">
                <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="scan-barcode" class="w-7 h-7 text-white"></i>
                </div>
                <h1 class="text-lg font-bold">Ambil Barang</h1>
                <p class="text-xs text-blue-100 mt-1">Konfirmasi jumlah yang Anda ambil.</p>
            </div>

            <div class="p-6">
                {{-- INFO BARANG --}}
                <div class="mb-6 text-center">
                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Nama Barang</p>
                    <h2 class="text-xl font-bold text-gray-800">{{ $stock->item->item_name }}</h2>
                    <p class="text-xs font-mono text-gray-500">{{ $stock->item->item_code }}</p>
                    
                    <div class="mt-3 inline-block bg-gray-100 px-3 py-1.5 rounded-full">
                        <span class="text-xs text-gray-600">Sisa Stok: </span>
                        <span class="font-bold text-blue-600">{{ $stock->stock }}</span>
                    </div>
                </div>

                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-xs border border-red-200">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('inventory.scan.submit', $stock->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah yang Diambil</label>
                        <input type="number" name="used_stock" value="1" min="1" max="{{ $stock->stock }}" required
                               class="w-full text-center text-2xl font-bold h-14 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-0">
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Keperluan / Tujuan</label>
                        <textarea name="purpose" rows="2" required placeholder="Contoh: Untuk cetak laporan bulanan"
                                  class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-0 text-sm p-3"></textarea>
                    </div>

                    <div class="mb-5 bg-blue-50 p-3 rounded-lg flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-200 rounded-full flex items-center justify-center shrink-0">
                            <i data-lucide="user" class="w-4 h-4 text-blue-700"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-blue-600">Diambil oleh:</p>
                            <p class="text-xs font-bold text-blue-900">{{ auth()->user()->name }}</p>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-base h-12 rounded-xl transition shadow-lg mb-3">
                        Konfirmasi Ambil
                    </button>

                    <a href="{{ url()->previous() }}" class="block w-full text-center py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition text-sm">
                        Batal
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>

<!-- jaajajdjadjaj -->