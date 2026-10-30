@extends('layouts.app')

@section('title', 'Daftar Consumable Request')
@section('page-title', 'Consumable Request')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Consumable Requests
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Kelola, tinjau, dan konsolidasikan pengajuan barang consumable ke dalam PR.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if(auth()->user()->hasRole('Super Admin'))
                <button type="button" onclick="submitConsolidation()"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                    </svg>
                    Gabungkan ke PR
                </button>
            @endif

            <button type="button" onclick="openCreateModal()"
                    class="inline-flex items-center gap-2 rounded-md bg-slate-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-slate-900 shadow-sm hover:bg-slate-800 dark:hover:bg-gray-100 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Buat Request
            </button>
        </div>
    </div>

    {{-- FORM KONSOLIDASI (HIDDEN) --}}
    <form id="consolidationForm" action="{{ route('consumable-requests.consolidation.process') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="no" id="consolidationNo">
        <div id="consolidationInputs"></div>
    </form>

    {{-- MAIN CARD --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
        
        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-slate-600 focus:ring-slate-500 dark:bg-gray-700 dark:border-gray-600">
                        </th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pemohon</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($requests as $req)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-3 text-center">
                                @if($req->status == 'approved')
                                    <input type="checkbox" name="request_ids[]" value="{{ $req->id }}"
                                           class="request-checkbox rounded border-gray-300 text-slate-600 focus:ring-slate-500 dark:bg-gray-700 dark:border-gray-600">
                                @else
                                    <input type="checkbox" disabled
                                           class="rounded border-gray-200 bg-gray-100 dark:bg-gray-800 dark:border-gray-700 cursor-not-allowed opacity-50"
                                           title="Hanya status Approved yang dapat digabungkan">
                                @endif
                            </td>

                            <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-400">
                                #{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}
                            </td>

                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $req->request_by ?? $req->user->name ?? '-' }}
                            </td>

                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $req->subject ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                @php
                                    $statusMap = [
                                        'approved'  => ['bg-emerald-100 text-emerald-800 border-emerald-200', 'Approved'],
                                        'rejected'  => ['bg-red-100 text-red-800 border-red-200', 'Rejected'],
                                        'revision'  => ['bg-amber-100 text-amber-800 border-amber-200', 'Revision'],
                                        'converted' => ['bg-indigo-100 text-indigo-800 border-indigo-200', 'Converted to PR'],
                                        'pending'   => ['bg-gray-100 text-gray-800 border-gray-200', 'Pending'],
                                    ];
                                    $currStatus = $req->status ?? 'pending';
                                    $statusClass = $statusMap[$currStatus][0] ?? $statusMap['pending'][0];
                                    $statusLabel = $statusMap[$currStatus][1] ?? 'Pending';
                                @endphp
                                <span class="inline-flex items-center rounded-md border px-2 py-1 text-xs font-medium {{ $statusClass }} dark:bg-opacity-20 dark:border-opacity-30">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Detail --}}
                                    <a href="{{ route('consumable-requests.show', $req->id) }}"
                                       title="Lihat Detail"
                                       class="p-1.5 text-gray-400 hover:text-slate-900 dark:hover:text-white rounded transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                   {{-- Aksi Cepat Super Admin (Hanya Pending) --}}
@if(auth()->user()->hasRole('Super Admin') && $req->status == 'pending')
    <div class="w-px h-4 bg-gray-300 dark:bg-gray-600 mx-1"></div>

    {{-- Approve --}}
    <form action="{{ route('consumable-requests.update-status', $req->id) }}" method="POST" class="inline">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" value="approved">
        <button type="submit" title="Setujui"
                class="p-1.5 text-gray-400 hover:text-emerald-600 rounded transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </button>
    </form>

    {{-- Revisi --}}
    <button type="button"
            title="Revisi"
            onclick="openRevisionModal('{{ route('consumable-requests.update-status', $req->id) }}')"
            class="p-1.5 text-gray-400 hover:text-amber-600 rounded transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
    </button>

    {{-- Reject --}}
    <form action="{{ route('consumable-requests.update-status', $req->id) }}" method="POST" class="inline">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" value="rejected">
        <button type="submit" title="Tolak"
                class="p-1.5 text-gray-400 hover:text-red-600 rounded transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </form>
@endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500 text-sm">
                                Belum ada data consumable request.
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

{{-- Modal Create --}}
@include('consumable-requests._modal')
@include('consumable-requests._revision-modal')
@endsection

@push('scripts')
<script>
    // Select All
    document.getElementById('selectAll')?.addEventListener('change', function () {
        document.querySelectorAll('.request-checkbox:not(:disabled)').forEach(cb => cb.checked = this.checked);
    });

    // Submit Konsolidasi
    function submitConsolidation() {
        const checked = document.querySelectorAll('.request-checkbox:checked');
        if (checked.length === 0) {
            alert('Pilih minimal 1 request dengan status Approved terlebih dahulu.');
            return;
        }
        window.__selectedRequestIds = Array.from(checked).map(cb => cb.value);
        window.dispatchEvent(new CustomEvent('pr-open'));
    }

    // Event listener form konsolidasi
    window.addEventListener('pr-confirm', function (e) {
        const form = document.getElementById('consolidationForm');
        const inputsContainer = document.getElementById('consolidationInputs');

        document.getElementById('consolidationNo').value = e.detail.no;
        inputsContainer.innerHTML = '';

        ['background', 'purpose', 'required_spec'].forEach(fieldName => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = fieldName;
            input.value = e.detail[fieldName] || '';
            inputsContainer.appendChild(input);
        });

        (window.__selectedRequestIds || []).forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'request_ids[]';
            input.value = id;
            inputsContainer.appendChild(input);
        });

        form.submit();
    });
</script>
@include('consumable-requests._scripts')
@endpush