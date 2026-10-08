@extends('layouts.app')

@section('title', 'Daftar Consumable Request')
@section('page-title', 'Daftar Consumable Request')

@section('content')
<div class="space-y-6 pb-12">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Consumable Request
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Kelola, tinjau, dan konsolidasikan pengajuan barang consumable ke dalam PR.
            </p>
        </div>

        <button type="button" onclick="openCreateModal()"
                class="inline-flex items-center gap-2 self-start rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Buat Request Baru
        </button>
    </div>

    {{-- FORM KONSOLIDASI --}}
    <form id="consolidationForm"
          action="{{ route('consumable-requests.consolidation.process') }}"
          method="POST"
          class="hidden">
        @csrf
        <input type="hidden" name="no" id="consolidationNo">
        <div id="consolidationInputs"></div>
    </form>

    {{-- MAIN CARD --}}
    <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">

        <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Semua Pengajuan</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total {{ $requests->total() ?? $requests->count() }} pengajuan tercatat
                    </p>
                </div>
            </div>

            @if(auth()->user()->hasRole('Super Admin'))
                <button type="button"
                        onclick="submitConsolidation()"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 px-4 py-2 text-xs font-semibold text-white shadow-sm transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-6 9l2 2 4-4" />
                    </svg>
                    Gabungkan Terpilih ke PR (Cetak)
                </button>
            @endif
        </header>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/40">
                        <th class="px-4 py-3.5 text-center w-12">
                            <input type="checkbox" id="selectAll"
                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">ID</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pemohon</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Subject</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70">
                    @forelse($requests as $req)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-4 text-center">
                                @if($req->status == 'approved')
                                    <input type="checkbox" name="request_ids[]" value="{{ $req->id }}"
                                           class="request-checkbox rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                @else
                                    <input type="checkbox" disabled
                                           class="rounded border-gray-200 bg-gray-100 dark:bg-gray-800 cursor-not-allowed opacity-50"
                                           title="Hanya status approved yang bisa digabungkan">
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700/60 px-2 py-1 text-xs font-mono font-semibold text-gray-700 dark:text-gray-300">
                                    #{{ $req->id }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $req->request_by ?? $req->user->name ?? '-' }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $req->subject ?? '-' }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                @if($req->status == 'approved')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-200 dark:ring-emerald-900/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Approved
                                    </span>
                                @elseif($req->status == 'rejected')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 dark:bg-red-500/10 px-2.5 py-1 text-xs font-semibold text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-200 dark:ring-red-900/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Rejected
                                    </span>
                                @elseif($req->status == 'revision')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:text-amber-400 ring-1 ring-inset ring-amber-200 dark:ring-amber-900/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Revision
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 dark:bg-blue-500/10 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:text-blue-400 ring-1 ring-inset ring-blue-200 dark:ring-blue-900/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                        Pending
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('consumable-requests.show', $req->id) }}"
                                       title="Detail"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    @if(auth()->user()->hasRole('Super Admin') && $req->status == 'pending')
                                        <form action="{{ route('consumable-requests.update-status', $req->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" title="Approve"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-600 dark:hover:text-emerald-400 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>

                                        <a href="{{ route('consumable-requests.edit', $req->id) }}"
                                           title="Edit"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 hover:text-amber-600 dark:hover:text-amber-400 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        <form action="{{ route('consumable-requests.destroy', $req->id) }}"
                                              method="POST" class="inline"
                                              data-confirm="Yakin ingin menghapus request ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 dark:hover:text-red-400 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-400 text-sm">
                                Belum ada consumable request tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                {{ $requests->links() }}
            </div>
        @endif
    </section>
</div>

{{-- Modal Create --}}
@include('consumable-requests._modal')
@endsection

@push('scripts')
{{-- Script khusus admin: select all & konsolidasi --}}
<script>
    // ═══ Select All ═══
    document.getElementById('selectAll')?.addEventListener('change', function () {
        document.querySelectorAll('.request-checkbox:not(:disabled)').forEach(cb => cb.checked = this.checked);
    });

    // ═══ Submit Konsolidasi — buka modal nomor PR dulu ═══
    function submitConsolidation() {
        const checked = document.querySelectorAll('.request-checkbox:checked');

        if (checked.length === 0) {
            alert('Pilih minimal 1 request dengan status Approved terlebih dahulu.');
            return;
        }

        window.__selectedRequestIds = Array.from(checked).map(cb => cb.value);
        window.dispatchEvent(new CustomEvent('pr-open'));
    }

    // ═══ Setelah user isi form & klik "Lanjut Cetak" ═══
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

{{-- Script modal create & form --}}
@include('consumable-requests._scripts')
@endpush