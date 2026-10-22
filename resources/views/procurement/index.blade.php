@extends('layouts.app')

@section('title', 'Procurement Requests')
@section('page-title', 'Procurement Requests')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">
    {{-- Flash Message --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/30 dark:border-emerald-800/50 dark:text-emerald-300 shadow-sm transition">
            <div class="p-1 rounded-md bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Procurement Request</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola persetujuan dan pantau status dokumen pengadaan barang & jasa.</p>
        </div>
        <a href="{{ route('procurement.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-gray-100 rounded-md shadow-sm transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i> Buat PR Baru
        </a>
    </div>

    {{-- TAB FILTER SUMBER PR --}}
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
            <a href="{{ route('procurement.index') }}" 
               class="whitespace-nowrap pb-3 px-1 border-b-2 text-sm font-medium transition-colors
                      {{ !request('source') ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                Semua PR
            </a>
            <a href="{{ route('procurement.index', ['source' => 'manual']) }}" 
               class="whitespace-nowrap pb-3 px-1 border-b-2 text-sm font-medium transition-colors
                      {{ request('source') == 'manual' ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                PR Langsung (Manual)
            </a>
            <a href="{{ route('procurement.index', ['source' => 'consumable']) }}" 
               class="whitespace-nowrap pb-3 px-1 border-b-2 text-sm font-medium transition-colors
                      {{ request('source') == 'consumable' ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                PR dari Consumable
            </a>
        </nav>
    </div>

    {{-- Data Table --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Dokumen</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Request By</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Divisi</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Qty</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($requests as $r)
                        @php
                            $statusColors = [
                                'pending'  => 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
                                'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/50',
                                'rejected' => 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800/50',
                                'revision' => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800/50',
                            ];
                            $statusLabels = [
                                'pending'  => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'revision' => 'Perlu Revisi',
                            ];
                            $currStatus = $r->status ?? 'pending';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            {{-- KOLOM NOMOR PR & BADGE SUMBER --}}
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $r->no }}</div>
                                <div class="mt-1">
                                    @if($r->source === 'consumable')
                                        <span class="inline-flex items-center rounded border border-blue-200 bg-blue-50 px-1.5 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800/50">
                                            CONSUMABLE
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                                            MANUAL
                                        </span>
                                    @endif
                                </div>
                            </td>
                            
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $r->date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $r->request_by }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $r->division }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300 max-w-xs truncate" title="{{ $r->description }}">{{ $r->description }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray-900 dark:text-white">{{ $r->quantity }}</td>
                            
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded border text-xs font-medium {{ $statusColors[$currStatus] ?? $statusColors['pending'] }}">
                                    {{ $statusLabels[$currStatus] ?? ucfirst($currStatus) }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Aksi Approval (Membuka Modal Modern) --}}
                                    <button type="button" 
                                            onclick="openActionModal('{{ route('procurement.status', $r) }}', 'approved', '{{ $r->no }}')" 
                                            class="p-1.5 text-gray-400 hover:text-emerald-600 rounded transition" 
                                            title="Setujui Pengajuan">
                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                    </button>

                                    <button type="button" 
                                            onclick="openActionModal('{{ route('procurement.status', $r) }}', 'revision', '{{ $r->no }}')" 
                                            class="p-1.5 text-gray-400 hover:text-amber-600 rounded transition" 
                                            title="Minta Revisi">
                                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                    </button>

                                    <button type="button" 
                                            onclick="openActionModal('{{ route('procurement.status', $r) }}', 'rejected', '{{ $r->no }}')" 
                                            class="p-1.5 text-gray-400 hover:text-rose-600 rounded transition" 
                                            title="Tolak Pengajuan">
                                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                                    </button>

                                    <div class="w-px h-4 bg-gray-300 dark:bg-gray-600 mx-1"></div>

                                    {{-- Tombol Aksi Lainnya --}}
                                    <a href="{{ route('procurement.comparisons.index', $r) }}" class="p-1.5 text-gray-400 hover:text-indigo-600 rounded transition" title="Vendor Comparison">
                                        <i data-lucide="git-compare" class="w-4 h-4"></i>
                                    </a>

                                    <a href="{{ route('procurement.show', $r) }}" class="p-1.5 text-gray-400 hover:text-slate-900 dark:hover:text-white rounded transition" title="Lihat Detail">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>

                                    <button type="button" 
                                            onclick="printDirect('{{ route('procurement.print', $r) }}')" 
                                            class="p-1.5 text-gray-400 hover:text-blue-600 rounded transition" 
                                            title="Print Form">
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </button>

                                    <a href="{{ route('procurement.pdf', $r) }}" class="p-1.5 text-gray-400 hover:text-teal-600 rounded transition" title="Simpan PDF">
                                        <i data-lucide="file-down" class="w-4 h-4"></i>
                                    </a>

                                    <a href="{{ route('procurement.edit', $r) }}" class="p-1.5 text-gray-400 hover:text-amber-600 rounded transition" title="Edit Data">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-gray-600"></i>
                                <p class="text-sm">Belum ada data Procurement Request.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Approval & Review --}}
<div id="actionModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeActionModal()"></div>

        {{-- Modal Content --}}
        <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
            <form id="actionModalForm" action="" method="POST" class="p-6">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" id="modalStatusInput" value="">

                <div class="flex items-start gap-4">
                    {{-- Icon Container --}}
                    <div id="modalIconContainer" class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full">
                        <i id="modalIcon" data-lucide="help-circle" class="w-5 h-5"></i>
                    </div>

                    <div class="flex-1 mt-1">
                        <h3 id="modalTitle" class="text-base font-semibold text-gray-900 dark:text-white leading-6"></h3>
                        <p id="modalDocNumber" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"></p>
                    </div>
                </div>

                {{-- Catatan / Review Notes Area --}}
                <div id="notesContainer" class="mt-5 space-y-1.5">
                    <label for="review_notes" id="notesLabel" class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                        Catatan & Alasan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="review_notes" id="review_notes" rows="3" 
                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white p-3 focus:ring-2 focus:ring-slate-500 focus:border-slate-500 focus:outline-none transition" 
                        placeholder="Berikan alasan atau instruksi yang jelas..."></textarea>
                </div>

                {{-- Modal Action Buttons --}}
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeActionModal()" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit" id="modalSubmitBtn" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg text-white shadow-sm transition">
                        Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function printDirect(url) {
    let iframe = document.getElementById('printFrame');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'printFrame';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);
    }
    
    iframe.src = url;
    iframe.onload = function() {
        setTimeout(function() {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }, 300);
    };
}

function openActionModal(actionUrl, actionType, docNo) {
    const modal     = document.getElementById('actionModal');
    const form      = document.getElementById('actionModalForm');
    const title     = document.getElementById('modalTitle');
    const docText   = document.getElementById('modalDocNumber');
    const input     = document.getElementById('modalStatusInput');
    const btn       = document.getElementById('modalSubmitBtn');
    const iconCont  = document.getElementById('modalIconContainer');
    const icon      = document.getElementById('modalIcon');
    const notesInput= document.getElementById('review_notes');
    const notesLabel= document.getElementById('notesLabel');

    form.action = actionUrl;
    input.value = actionType;
    docText.innerText = 'Dokumen: ' + docNo;
    modal.classList.remove('hidden');

    if (actionType === 'approved') {
        title.innerText = 'Setujui Pengajuan';
        iconCont.className = 'flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400';
        icon.setAttribute('data-lucide', 'check');
        btn.className = 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition';
        btn.innerText = 'Setujui';
        
        notesLabel.innerHTML = 'Catatan Tambahan <span class="text-xs text-gray-400 font-normal">(Opsional)</span>';
        notesInput.required = false;
        notesInput.placeholder = 'Tambahkan catatan jika diperlukan...';
    } 
    else if (actionType === 'revision') {
        title.innerText = 'Minta Revisi';
        iconCont.className = 'flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400';
        icon.setAttribute('data-lucide', 'rotate-ccw');
        btn.className = 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition';
        btn.innerText = 'Kirim Revisi';
        
        notesLabel.innerHTML = 'Instruksi Revisi <span class="text-rose-500">*</span>';
        notesInput.required = true;
        notesInput.placeholder = 'Jelaskan bagian mana yang perlu diperbaiki...';
    } 
    else if (actionType === 'rejected') {
        title.innerText = 'Tolak Pengajuan';
        iconCont.className = 'flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400';
        icon.setAttribute('data-lucide', 'x');
        btn.className = 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition';
        btn.innerText = 'Tolak';
        
        notesLabel.innerHTML = 'Alasan Penolakan <span class="text-rose-500">*</span>';
        notesInput.required = true;
        notesInput.placeholder = 'Jelaskan alasan pengajuan ini ditolak...';
    }

    if (window.lucide) {
        lucide.createIcons();
    }
}

function closeActionModal() {
    document.getElementById('actionModal').classList.add('hidden');
    document.getElementById('review_notes').value = '';
}
</script>
@endpush