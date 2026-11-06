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
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage approvals and track the status of goods & services procurement documents.</p>
        </div>
        <a href="{{ route('procurement.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-gray-100 rounded-md shadow-sm transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i> Create New PR
        </a>
    </div>

    {{-- TAB FILTER --}}
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
            <a href="{{ route('procurement.index') }}" 
               class="whitespace-nowrap pb-3 px-1 border-b-2 text-sm font-medium transition-colors
                      {{ !request('source') && !request('stage') ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                All PRs
            </a>
            <a href="{{ route('procurement.index', ['source' => 'manual']) }}" 
               class="whitespace-nowrap pb-3 px-1 border-b-2 text-sm font-medium transition-colors
                      {{ request('source') == 'manual' ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                Direct PR (Manual)
            </a>
            <a href="{{ route('procurement.index', ['source' => 'consumable']) }}" 
               class="whitespace-nowrap pb-3 px-1 border-b-2 text-sm font-medium transition-colors
                      {{ request('source') == 'consumable' ? 'border-slate-900 text-slate-900 dark:border-white dark:text-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                PR from Consumable
            </a>
            
            {{-- TAB BARU: PR FINAL (TAHAP 2) --}}
            <a href="{{ route('procurement.index', ['stage' => 'final']) }}" 
               class="whitespace-nowrap pb-3 px-1 border-b-2 text-sm font-medium transition-colors flex items-center gap-2
                      {{ request('stage') == 'final' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                PR Final (Tahap 2)
                <span class="bg-indigo-100 text-indigo-700 text-[10px] px-2 py-0.5 rounded-full dark:bg-indigo-900/50 dark:text-indigo-300">Final</span>
            </a>
        </nav>
    </div>

    {{-- Data Table --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Document No.</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Method</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Request By</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Division</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Qty</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($requests as $r)
                        @php
                            $statusColors = [
                                'pending'        => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800/50',
                                'approved'       => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/50',
                                'rejected'       => 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800/50',
                                'revision'       => 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800/50',
                                'processed'      => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800/50',
                                'final_approved' => 'bg-indigo-100 text-indigo-800 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800/50',
                                'received'       => 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800/50',
                                'completed'      => 'bg-slate-100 text-slate-800 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                            ];
                            
                            $currStatus = $r->status ?? 'pending';
                            
                            $statusLabels = [
                                'pending'        => 'Pending',
                                'approved'       => 'Approved',
                                'rejected'       => 'Rejected',
                                'revision'       => 'Needs Revision',
                                'processed'      => 'Processed (Purchased)',
                                'final_approved' => 'Final Approved',
                                'received'       => 'Received',
                                'completed'      => 'Completed',
                            ];
                            $statusLabel = $statusLabels[$currStatus] ?? ucwords(str_replace('_', ' ', $currStatus));

                            $methodColors = [
                                'po'             => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/50',
                                'cash_advance'   => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800/50',
                                'reimbursement'  => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-400 dark:border-indigo-800/50',
                            ];
                            $methodLabels = [
                                'po'             => 'PO',
                                'cash_advance'   => 'Cash Advance',
                                'reimbursement'  => 'Reimbursement',
                            ];
                            $currMethod = $r->procurement_method ?? 'po';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
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

                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded border px-2 py-0.5 text-xs font-medium {{ $methodColors[$currMethod] ?? $methodColors['po'] }}">
                                    {{ $methodLabels[$currMethod] ?? 'PO' }}
                                </span>
                            </td>
                            
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $r->date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $r->request_by }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                    {{ $r->division }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300 max-w-xs truncate" title="{{ $r->description }}">{{ $r->description }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray-900 dark:text-white">{{ $r->quantity + 0 }}</td>
                            
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md border text-xs font-semibold {{ $statusColors[$currStatus] ?? $statusColors['pending'] }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Approval Actions (Hanya Muncul Jika Pending) --}}
                                    @if($currStatus === 'pending')
                                        <button type="button" onclick="openActionModal('{{ route('procurement.status', $r) }}', 'approved', '{{ $r->no }}')" class="p-1.5 text-gray-400 hover:text-emerald-600 rounded transition" title="Approve Request">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="openActionModal('{{ route('procurement.status', $r) }}', 'revision', '{{ $r->no }}')" class="p-1.5 text-gray-400 hover:text-amber-600 rounded transition" title="Request Revision">
                                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="openActionModal('{{ route('procurement.status', $r) }}', 'rejected', '{{ $r->no }}')" class="p-1.5 text-gray-400 hover:text-rose-600 rounded transition" title="Reject Request">
                                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                                        </button>
                                        <div class="w-px h-4 bg-gray-300 dark:bg-gray-600 mx-1"></div>
                                    @endif

                                    <a href="{{ route('procurement.comparisons.index', $r) }}" class="p-1.5 text-gray-400 hover:text-indigo-600 rounded transition" title="Vendor Comparison">
                                        <i data-lucide="git-compare" class="w-4 h-4"></i>
                                    </a>

                                    <a href="{{ route('procurement.show', $r) }}" class="p-1.5 text-gray-400 hover:text-slate-900 dark:hover:text-white rounded transition" title="View Details">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>

                                    {{-- TOMBOL PRINT PINTAR (Pilih template berdasarkan status) --}}
                                    <button type="button" 
                                            onclick="printDirect('{{ in_array($currStatus, ['processed', 'final_approved', 'received', 'completed']) ? route('procurement.print.final', $r) : route('procurement.print', $r) }}')" 
                                            class="p-1.5 text-gray-400 hover:text-blue-600 rounded transition" 
                                            title="Print Form">
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </button>

                                    {{-- TOMBOL PDF PINTAR --}}
                                    <a href="{{ in_array($currStatus, ['processed', 'final_approved', 'received', 'completed']) ? route('procurement.pdf.final', $r) : route('procurement.pdf', $r) }}" class="p-1.5 text-gray-400 hover:text-teal-600 rounded transition" title="Download PDF">
                                        <i data-lucide="file-down" class="w-4 h-4"></i>
                                    </a>

                                    @if(in_array($currStatus, ['pending', 'revision']))
                                        <a href="{{ route('procurement.edit', $r) }}" class="p-1.5 text-gray-400 hover:text-amber-600 rounded transition" title="Edit Data">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-gray-600"></i>
                                <p class="text-sm">No Procurement Request data yet.</p>
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

{{-- Approval & Review Modal --}}
<div id="actionModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeActionModal()"></div>
        <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
            <form id="actionModalForm" action="" method="POST" class="p-6">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" id="modalStatusInput" value="">

                <div class="flex items-start gap-4">
                    <div id="modalIconContainer" class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full">
                        <i id="modalIcon" data-lucide="help-circle" class="w-5 h-5"></i>
                    </div>
                    <div class="flex-1 mt-1">
                        <h3 id="modalTitle" class="text-base font-semibold text-gray-900 dark:text-white leading-6"></h3>
                        <p id="modalDocNumber" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"></p>
                    </div>
                </div>

                <div id="notesContainer" class="mt-5 space-y-1.5">
                    <label for="review_notes" id="notesLabel" class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                        Notes & Reason <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="review_notes" id="review_notes" rows="3" 
                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white p-3 focus:ring-2 focus:ring-slate-500 focus:border-slate-500 focus:outline-none transition" 
                        placeholder="Provide a clear reason or instruction..."></textarea>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeActionModal()" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Cancel
                    </button>
                    <button type="submit" id="modalSubmitBtn" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg text-white shadow-sm transition">
                        Confirm
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
    docText.innerText = 'Document: ' + docNo;
    modal.classList.remove('hidden');

    if (actionType === 'approved') {
        title.innerText = 'Approve Request';
        iconCont.className = 'flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400';
        icon.setAttribute('data-lucide', 'check');
        btn.className = 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition';
        btn.innerText = 'Approve';
        notesLabel.innerHTML = 'Additional Notes <span class="text-xs text-gray-400 font-normal">(Optional)</span>';
        notesInput.required = false;
        notesInput.placeholder = 'Add a note if necessary...';
    } 
    else if (actionType === 'revision') {
        title.innerText = 'Request Revision';
        iconCont.className = 'flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400';
        icon.setAttribute('data-lucide', 'rotate-ccw');
        btn.className = 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition';
        btn.innerText = 'Send Revision';
        notesLabel.innerHTML = 'Revision Instructions <span class="text-rose-500">*</span>';
        notesInput.required = true;
        notesInput.placeholder = 'Explain which parts need to be fixed...';
    } 
    else if (actionType === 'rejected') {
        title.innerText = 'Reject Request';
        iconCont.className = 'flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400';
        icon.setAttribute('data-lucide', 'x');
        btn.className = 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition';
        btn.innerText = 'Reject';
        notesLabel.innerHTML = 'Rejection Reason <span class="text-rose-500">*</span>';
        notesInput.required = true;
        notesInput.placeholder = 'Explain why this request is rejected...';
    }
    if (window.lucide) lucide.createIcons();
}

function closeActionModal() {
    document.getElementById('actionModal').classList.add('hidden');
    document.getElementById('review_notes').value = '';
}
</script>
@endpush