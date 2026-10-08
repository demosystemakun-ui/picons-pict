@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <!-- Tombol Aksi / Toolbar (Akan disembunyikan saat dicetak) -->
    <div class="flex flex-wrap items-center justify-between gap-3 no-print">
        <h1 class="text-xl font-semibold text-gray-900">{{ $reimbursement->number }}</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reimbursement.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Kembali</a>
            
            <!-- Tombol Cetak Langsung (window.print) -->
            <button onclick="window.print()" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Cetak</button>
            
            <a href="{{ route('reimbursement.pdf', $reimbursement) }}" target="_blank" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Unduh PDF</a>
            <a href="{{ route('reimbursement.edit', $reimbursement) }}" class="rounded-lg bg-blue-700 px-3 py-2 text-sm font-medium text-white hover:bg-blue-800">Ubah</a>
            
            <form method="POST" action="{{ route('reimbursement.destroy', $reimbursement) }}" onsubmit="return confirm('Hapus form ini?')" class="inline">
                @csrf @method('DELETE')
                <button class="rounded-lg border border-red-300 px-3 py-2 text-sm text-red-700 hover:bg-red-50">Hapus</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 no-print">{{ session('success') }}</div>
    @endif

    <!-- Area Dokumen Reimbursement -->
    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white p-6 shadow-sm document-card">
        <div class="mx-auto" style="min-width:720px; max-width:820px;">
            @include('procurement.reimbursement._document', ['logo' => asset('logo/pict.png')])
        </div>
    </div>
</div>

<!-- Styling khusus untuk mencetak (Print Mode) -->
<style>
    @media print {
        /* Sembunyikan navigasi layout utama, tombol aksi, dan alert */
        body {
            background: #fff !important;
        }
        header, nav, aside, footer, .no-print {
            display: none !important;
        }
        /* Hilangkan pembungkus card agar pas di kertas cetak */
        .document-card {
            overflow: visible !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            background: #fff !important;
        }
    }
</style>
@endsection