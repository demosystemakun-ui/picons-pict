@extends('layouts.app')

@section('title', 'My Dashboard')
@section('page-title', 'Dashboard Karyawan')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-12 pt-4 px-4">

    {{-- ═══ HEADER BANNER ═══ --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10">
            <h1 class="text-2xl sm:text-3xl font-bold">Halo, {{ auth()->user()->name }}! 👋</h1>
            <p class="text-blue-100 mt-2 text-sm max-w-xl">Selamat datang di portal pengajuan. Di sini Anda dapat memantau status pengajuan (PR) dan melihat riwayat barang yang sering Anda gunakan.</p>
        </div>
        <div class="absolute -right-10 -bottom-10 opacity-10">
            <i data-lucide="layout-dashboard" class="w-48 h-48"></i>
        </div>
    </div>

    {{-- ═══ STATISTIK PENGAJUAN ═══ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Total Pengajuan</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($my_pr_total) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-amber-200 shadow-sm border-l-4 border-l-amber-500">
            <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Menunggu Review</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($my_pr_pending) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-emerald-200 shadow-sm border-l-4 border-l-emerald-500">
            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Disetujui / Selesai</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($my_pr_approved) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-red-200 shadow-sm border-l-4 border-l-red-500">
            <p class="text-[10px] font-bold text-red-600 uppercase tracking-wider">Ditolak / Revisi</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($my_pr_rejected) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- ═══ RIWAYAT PENGAJUAN TERAKHIR ═══ --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h2 class="font-semibold text-gray-800">Pengajuan Terakhir</h2>
                <a href="{{ route('procurement.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">Lihat Semua →</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recent_prs as $pr)
                    <a href="{{ route('procurement.show', $pr->id) }}" class="block p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-medium text-sm text-gray-900 truncate">{{ $pr->description }}</p>
                                <p class="text-[11px] text-gray-500 mt-1">{{ $pr->no }} • {{ $pr->date->format('d M Y') }}</p>
                            </div>
                            <div class="shrink-0">
                                @if(in_array($pr->status, ['pending']))
                                    <span class="px-2 py-1 text-[10px] font-bold rounded bg-amber-100 text-amber-700 uppercase">Menunggu</span>
                                @elseif(in_array($pr->status, ['approved', 'processed', 'pending_review_final', 'pending_final_sign']))
                                    <span class="px-2 py-1 text-[10px] font-bold rounded bg-blue-100 text-blue-700 uppercase">Diproses</span>
                                @elseif(in_array($pr->status, ['final_approved', 'received', 'completed']))
                                    <span class="px-2 py-1 text-[10px] font-bold rounded bg-emerald-100 text-emerald-700 uppercase">Selesai</span>
                                @else
                                    <span class="px-2 py-1 text-[10px] font-bold rounded bg-red-100 text-red-700 uppercase">Revisi/Tolak</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-gray-400 text-sm">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 mb-3">
                            <i data-lucide="file-text" class="w-6 h-6"></i>
                        </div>
                        <p>Belum ada riwayat pengajuan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ═══ BARANG PALING SERING DIPAKAI ═══ --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                <h2 class="font-semibold text-gray-800">Barang Sering Dipakai</h2>
                <p class="text-[11px] text-gray-500 mt-0.5">Berdasarkan riwayat pengajuan Anda</p>
            </div>
            <div class="p-5">
                @forelse($most_used_items as $usage)
                    <div class="flex items-center justify-between mb-4 last:mb-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                                <i data-lucide="package" class="w-5 h-5"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $usage->item_name ?? 'Unknown Item' }}</p>
                                <p class="text-[11px] text-gray-500">Sering digunakan</p>
                            </div>
                        </div>
                        <span class="font-bold text-gray-700 bg-gray-100 px-3 py-1 rounded-lg text-sm">{{ $usage->total_used }}x</span>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 mb-3 text-gray-400">
                            <i data-lucide="inbox" class="w-6 h-6"></i>
                        </div>
                        <p class="text-sm text-gray-500">Belum ada data pemakaian barang.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection