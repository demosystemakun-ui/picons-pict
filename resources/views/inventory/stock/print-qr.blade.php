<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR - {{ $department->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; margin: 0; padding: 0; }
            .qr-card { box-shadow: none !important; border: 2px solid #000 !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="text-center">
        {{-- KARTU QR CODE --}}
        <div class="qr-card bg-white rounded-2xl shadow-xl border border-gray-200 p-8 max-w-sm mx-auto inline-block">
            <h2 class="text-xl font-bold text-gray-800 mb-1">Lemari / Departemen</h2>
            <h1 class="text-3xl font-extrabold text-blue-600 mb-4">{{ $department->name }}</h1>
            
            {{-- Gambar QR Code Otomatis dari API --}}
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($scanUrl) }}" 
                 alt="QR Code {{ $department->name }}" 
                 class="w-64 h-64 mx-auto rounded-lg border-4 border-white shadow-sm">
            
            <p class="mt-4 text-sm font-medium text-gray-600">
                <span class="block text-2xl mb-1">📱</span>
                Scan untuk melihat daftar barang & mengambil stok
            </p>
            <p class="text-[10px] text-gray-400 mt-2 break-all">{{ $scanUrl }}</p>
        </div>

        {{-- TOMBOL AKSI (Hilang saat di-print) --}}
        <div class="mt-8 no-print flex justify-center gap-3">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold shadow-md flex items-center gap-2 transition">
                <i data-lucide="printer" class="w-5 h-5"></i> Cetak QR Code
            </button>
            <button onclick="window.close()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-semibold shadow-md transition">
                Tutup
            </button>
        </div>
    </div>

    {{-- Load Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>