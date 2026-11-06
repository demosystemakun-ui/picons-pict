@extends('layouts.app')

@section('title', 'Procurement Request Detail')
@section('page-title', 'Procurement Request Detail')

@php
    /* ═══ STATUS CONFIG ═══ */
    $statusMeta = [
        'pending'   => ['label' => 'Pending Review',          'color' => 'bg-amber-500'],
        'approved'  => ['label' => 'Pending TTD',             'color' => 'bg-blue-500'],
        'rejected'  => ['label' => 'Rejected',                'color' => 'bg-red-500'],
        'revision'  => ['label' => 'Needs Revision',          'color' => 'bg-orange-500'],
        'processed' => ['label' => 'Processed (Purchased)',   'color' => 'bg-indigo-500'],
        'received'  => ['label' => 'Received',                'color' => 'bg-purple-500'],
        'completed' => ['label' => 'Completed',               'color' => 'bg-slate-500'],
    ];
    $currStatus = $procurementRequest->status ?? 'pending';
    $meta       = $statusMeta[$currStatus] ?? $statusMeta['pending'];

    /* ═══ ORDER TYPES ═══ */
    $orderTypes = array_filter([
        $procurementRequest->is_new_order    ? 'New Order'    : null,
        $procurementRequest->is_repeat_order ? 'Repeat Order' : null,
        $procurementRequest->is_goods        ? 'Goods'        : null,
        $procurementRequest->is_services     ? 'Services'     : null,
    ]);

    /* ═══ APPROVAL CHAIN ═══ */
    $approvals = [
        ['label' => 'PIC',         'value' => $procurementRequest->pic_name],
        ['label' => 'Ops Manager', 'value' => $procurementRequest->ops_manager_name],
        ['label' => 'FEM Manager', 'value' => $procurementRequest->fem_manager_name],
        ['label' => 'COO',         'value' => $procurementRequest->coo_name],
    ];

    $canCreateCA = auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Admin Departemen']);

    // Cek apakah semua TTD sudah di-approve
    $isAllSigned = $procurementRequest->is_pic_approved 
                && $procurementRequest->is_ops_approved 
                && $procurementRequest->is_fem_approved 
                && $procurementRequest->is_coo_approved;
@endphp

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12 pt-6 px-4">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="{{ route('procurement.index') }}"
               class="mt-0.5 inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition shrink-0">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-gray-900 dark:text-white font-mono">
                        {{ $procurementRequest->no }}
                    </h1>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-md border border-gray-200 dark:border-gray-700">
                        <span class="h-1.5 w-1.5 rounded-full {{ $meta['color'] }} {{ $currStatus === 'pending' ? 'animate-pulse' : '' }}"></span>
                        {{ $meta['label'] }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Created on {{ $procurementRequest->created_at->format('d M Y, H:i') }} WIB
                </p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2">
            @if($currStatus === 'pending')
                <form action="{{ route('procurement.status', $procurementRequest) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memvalidasi format PR ini?')" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Approve Format
                    </button>
                </form>
                <button type="button" onclick="openActionModal('revision')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Revision
                </button>
                <button type="button" onclick="openActionModal('rejected')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition">
                    <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Reject
                </button>
            @elseif($currStatus === 'revision')
                <form action="{{ route('procurement.status', $procurementRequest) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="pending">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition">
                        <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i> Resubmit
                    </button>
                </form>
            @elseif($currStatus === 'approved')
                {{-- Tombol ini HANYA muncul jika semua TTD sudah di-approve --}}
                @if($isAllSigned)
                    @if($procurementRequest->vendors->isEmpty())
                        <a href="{{ route('procurement.comparisons.index', $procurementRequest) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-primary-600 hover:bg-primary-700 text-white shadow-sm transition">
                            <i data-lucide="scale" class="w-3.5 h-3.5"></i> Price Comparison
                        </a>
                    @elseif(!$procurementRequest->winnerVendor)
                        <a href="{{ route('procurement.comparisons.index', $procurementRequest) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition">
                            <i data-lucide="check-square" class="w-3.5 h-3.5"></i> Select Winner
                        </a>
                    @else
                        <a href="{{ route('procurement.purchases.create', $procurementRequest) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition">
                            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i> Process PO
                        </a>
                    @endif
                @else
                    <button type="button" disabled class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-not-allowed border border-gray-200 dark:border-gray-700" title="Selesaikan semua validasi TTD terlebih dahulu">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i> Waiting for Signatures
                    </button>
                @endif
            @elseif($currStatus === 'received')
                <form action="{{ route('procurement.status', $procurementRequest) }}" method="POST" class="inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-slate-700 hover:bg-slate-800 text-white shadow-sm transition">
                        <i data-lucide="badge-check" class="w-3.5 h-3.5"></i> Complete
                    </button>
                </form>
            @endif

            {{-- Cash Advance & Liquidation --}}
            @if($canCreateCA && (in_array($currStatus, ['processed', 'received']) || ($currStatus === 'approved' && $isAllSigned)))
                <a href="{{ route('cash-advances.create', ['procurement_request_id' => $procurementRequest->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md border border-purple-200 dark:border-purple-800/60 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 hover:bg-purple-100 transition">
                    <i data-lucide="banknote" class="w-3.5 h-3.5"></i> Cash Advance
                </a>
                <a href="{{ route('liquidations.create', ['procurement_request_id' => $procurementRequest->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md border border-indigo-200 dark:border-indigo-800/60 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 transition">
                    <i data-lucide="file-check-2" class="w-3.5 h-3.5"></i> Liquidation
                </a>
            @endif

            {{-- Edit & Delete --}}
            @if(in_array($currStatus, ['pending', 'revision']))
                <a href="{{ route('procurement.edit', $procurementRequest) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                </a>
                <form action="{{ route('procurement.destroy', $procurementRequest) }}" method="POST" onsubmit="return confirm('Delete this request?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md border border-red-200 dark:border-red-800/60 bg-white dark:bg-gray-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                    </button>
                </form>
            @endif

            <span class="hidden sm:inline-block w-px h-5 bg-gray-300 dark:bg-gray-700 mx-1"></span>

            <a href="{{ route('procurement.print', $procurementRequest) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print
            </a>
            <a href="{{ route('procurement.pdf', $procurementRequest) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i data-lucide="download" class="w-3.5 h-3.5"></i> PDF
            </a>
        </div>
    </div>

    {{-- REVIEW NOTES ALERT --}}
    @if(!empty($procurementRequest->review_notes))
        @php
            $isRevision = $currStatus === 'revision';
            $alertStyle = $isRevision ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800/60' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/60';
            $alertText  = $isRevision ? 'text-amber-800 dark:text-amber-300' : 'text-red-800 dark:text-red-300';
            $alertIcon  = $isRevision ? 'text-amber-500' : 'text-red-500';
        @endphp
        <div class="rounded-lg {{ $alertStyle }} border px-4 py-3">
            <div class="flex gap-2.5">
                <svg class="h-4 w-4 flex-shrink-0 {{ $alertIcon }} mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="text-sm">
                    <p class="font-semibold {{ $alertText }}">{{ $isRevision ? 'Revision' : 'Rejection' }} Note</p>
                    <p class="mt-0.5 {{ $alertText }} opacity-90 whitespace-pre-line">{{ $procurementRequest->review_notes }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- MAIN CONTENT CARD --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700 overflow-hidden">

        {{-- SECTION 1: GENERAL INFORMATION --}}
        <section class="p-6">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">General Information</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 text-sm">
                <div class="sm:col-span-3">
                    <span class="text-xs text-gray-500 block mb-1">Request Date</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $procurementRequest->date->format('d F Y') }}</span>
                </div>
                <div class="sm:col-span-3">
                    <span class="text-xs text-gray-500 block mb-1">Division</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $procurementRequest->division }}</span>
                </div>
                <div class="sm:col-span-3">
                    <span class="text-xs text-gray-500 block mb-1">Requested By</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $procurementRequest->request_by }}</span>
                </div>
                <div class="sm:col-span-3">
                    <span class="text-xs text-gray-500 block mb-1">Prepared By</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $procurementRequest->prepared_by }}</span>
                </div>
                
                <div class="sm:col-span-12">
                    <span class="text-xs text-gray-500 block mb-1">Procurement Description</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $procurementRequest->description }}</p>
                </div>

                <div class="sm:col-span-12">
                    <span class="text-xs text-gray-500 block mb-2">Order Type</span>
                    <div class="flex flex-wrap gap-2">
                        @forelse($orderTypes as $type)
                            <span class="inline-flex items-center rounded bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                {{ $type }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-400 italic">— No order type —</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 2: REQUIREMENT DETAILS --}}
        <section class="p-6">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Requirement Details &amp; Justification</h2>
            <div class="space-y-4 text-sm">
                @php
                    $justifications = [
                        ['label' => 'Background',              'value' => $procurementRequest->background,      'pre' => false],
                        ['label' => 'Purpose',                 'value' => $procurementRequest->purpose,         'pre' => false],
                        ['label' => 'Required Specification',  'value' => $procurementRequest->required_spec,   'pre' => true],
                    ];
                @endphp

                @foreach($justifications as $j)
                    <div>
                        <span class="text-xs text-gray-500 block mb-1">{{ $j['label'] }}</span>
                        <p class="text-gray-900 dark:text-gray-200 leading-relaxed {{ $j['pre'] ? 'whitespace-pre-line' : '' }}">
                            {{ $j['value'] ?: '—' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- SECTION 3: REQUESTED ITEMS --}}
        <section class="p-6">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Requested Items</h2>

            @if($procurementRequest->items->isNotEmpty())
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide w-12">No.</th>
                                <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Item</th>
                                <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide w-2/5">Requirement Note</th>
                                <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide w-20 text-center">Qty</th>
                                <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide w-20 text-center">Unit</th>
                                <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide w-28">Picture</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($procurementRequest->items as $item)
                                <tr class="align-top hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                    <td class="py-3 px-4 text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                                    <td class="py-3 px-4">
                                        @php
                                            $lines = [];
                                            if (!empty($item->item_name)) $lines[] = $item->item_name;
                                            if (!empty($item->brand))     $lines[] = 'Brand : ' . $item->brand;
                                            if (!empty($item->type))      $lines[] = 'Type : ' . $item->type;
                                            if (!empty($item->model))     $lines[] = 'Model : ' . $item->model;
                                            if (!empty($item->capacity))  $lines[] = 'Capacity : ' . $item->capacity;
                                            if (!empty($item->specs)) {
                                                $specsLines = array_filter(array_map('trim', explode("\n", $item->specs)));
                                                $lines = array_merge($lines, $specsLines);
                                            }
                                            if (empty($lines) && !empty($item->item_details)) $lines[] = $item->item_details;
                                        @endphp
                                        <div class="text-sm font-medium text-gray-900 dark:text-white leading-relaxed space-y-0.5">
                                            @forelse($lines as $line)
                                                <p>{{ $line }}</p>
                                            @empty
                                                <span class="text-gray-400 italic">—</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $item->item_requirement_note ?: '—' }}</td>
                                    <td class="py-3 px-4 text-center font-semibold text-gray-900 dark:text-white">{{ $item->quantity + 0 }}</td>
                                    <td class="py-3 px-4 text-center text-gray-600 dark:text-gray-300">{{ $item->unit ?: '—' }}</td>
                                    <td class="py-3 px-4">
                                        @if($item->picture)
                                            <a href="{{ Storage::url($item->picture) }}" target="_blank" rel="noopener">
                                                <img src="{{ Storage::url($item->picture) }}" alt="Item picture" class="h-10 w-10 object-cover rounded border border-gray-200 dark:border-gray-700">
                                            </a>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50/70 dark:bg-gray-900/40">
                            <tr>
                                <td colspan="3" class="py-3 px-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Qty</td>
                                <td class="py-3 px-4 text-center font-bold text-gray-900 dark:text-white">{{ $procurementRequest->items->sum('quantity') + 0 }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @if($procurementRequest->target_purchase)
                    <div class="mt-4 text-sm">
                        <span class="text-xs text-gray-500 block mb-1">Target Purchase</span>
                        <span class="text-gray-800 dark:text-gray-200">{{ $procurementRequest->target_purchase }}</span>
                    </div>
                @endif
            @else
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg border border-gray-200 dark:border-gray-700 p-4 text-sm grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-3">
                        <span class="text-xs text-gray-500 block mb-1">Item Name</span>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $procurementRequest->item_name ?: '—' }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block mb-1">Quantity</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $procurementRequest->quantity + 0 }}</span>
                    </div>
                    <div class="sm:col-span-4">
                        <span class="text-xs text-gray-500 block mb-1">Requirement Note</span>
                        <span class="text-gray-800 dark:text-gray-200">{{ $procurementRequest->item_requirement_note ?: '—' }}</span>
                    </div>
                    <div class="sm:col-span-4">
                        <span class="text-xs text-gray-500 block mb-1">Target Purchase</span>
                        <span class="text-gray-800 dark:text-gray-200">{{ $procurementRequest->target_purchase ?: '—' }}</span>
                    </div>
                </div>
            @endif
        </section>

        {{-- SECTION 4: SIGNATURES --}}
        <section class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Approval &amp; Signatures</h2>
                
                {{-- TOMBOL VALIDASI GLOBAL --}}
                @if(!$isAllSigned && in_array($currStatus, ['approved', 'processed']))
                    <form action="{{ route('procurement.approve-all-signatures', $procurementRequest) }}" method="POST" onsubmit="return confirm('Validasi semua tanda tangan (PR Awal & PR Final)?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                            <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                            Approve All Signatures
                        </button>
                    </form>
                @endif
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $signatures = [
                        ['label' => 'PIC',         'value' => $procurementRequest->pic_name,         'field' => 'pic_name',         'approved' => $procurementRequest->is_pic_approved],
                        ['label' => 'Ops Manager', 'value' => $procurementRequest->ops_manager_name, 'field' => 'ops_manager_name', 'approved' => $procurementRequest->is_ops_approved],
                        ['label' => 'FEM Manager', 'value' => $procurementRequest->fem_manager_name, 'field' => 'fem_manager_name', 'approved' => $procurementRequest->is_fem_approved],
                        ['label' => 'COO',         'value' => $procurementRequest->coo_name,         'field' => 'coo_name',         'approved' => $procurementRequest->is_coo_approved],
                    ];
                @endphp

                @foreach($signatures as $sig)
                    <div class="rounded-lg border {{ $sig['approved'] ? 'border-emerald-300 dark:border-emerald-700 bg-emerald-50/30 dark:bg-emerald-900/10' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30' }} p-3 text-center flex flex-col justify-between transition-colors">
                        
                        <div>
                            <span class="text-[10px] font-semibold uppercase tracking-wider {{ $sig['approved'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500' }} block mb-2">
                                {{ $sig['label'] }}
                            </span>
                            <p class="font-medium text-sm text-gray-800 dark:text-gray-200 truncate">{{ $sig['value'] ?: '—' }}</p>
                        </div>

                        {{-- STATUS & BUTTON --}}
                        <div class="mt-3 pt-3 border-t {{ $sig['approved'] ? 'border-emerald-200 dark:border-emerald-800/50' : 'border-gray-200 dark:border-gray-700' }}">
                            @if($sig['approved'])
                                <span class="inline-flex items-center justify-center gap-1 w-full text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Approved
                                </span>
                            @else
                                <button type="button" 
                                        onclick="validateSignature('{{ $sig['field'] }}', this)"
                                        class="w-full inline-flex items-center justify-center gap-1 px-2 py-1 text-[10px] font-semibold rounded bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    Validate Signature
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <p class="text-[11px] text-gray-500 mt-4 text-center">
                *Silakan validasi setiap kolom setelah tanda tangan basah dibubuhkan pada dokumen cetak.
            </p>
        </section>

    </div>
</div>

{{-- ACTION MODAL (Revision / Reject) --}}
<div id="actionModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeActionModal()"></div>
    <div class="relative z-10 flex items-center justify-center min-h-full p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-5 shadow-2xl border border-gray-200 dark:border-gray-700">
            <div class="flex items-start gap-3 mb-4">
                <div id="modalIconWrap" class="flex h-9 w-9 items-center justify-center rounded-full shrink-0"></div>
                <div class="min-w-0">
                    <h3 id="modalTitle" class="text-base font-bold text-gray-900 dark:text-white">Confirm Action</h3>
                    <p id="modalSubtitle" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Please enter the reason or note for this decision.</p>
                </div>
            </div>

            <form action="{{ route('procurement.status', $procurementRequest) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" id="modalStatusInput" value="">
                <div class="mb-4">
                    <label for="review_notes" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Note / Reason <span class="text-red-500">*</span></label>
                    <textarea name="review_notes" id="review_notes" rows="3" required placeholder="Write a detailed note..." class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white p-2.5 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeActionModal()" class="px-3 py-1.5 text-sm font-medium rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancel</button>
                    <button type="submit" id="modalSubmitBtn" class="px-3 py-1.5 text-sm font-medium rounded-md text-white shadow-sm transition">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    const MODAL_CONFIG = {
        revision: {
            title: 'Request Revision', subtitle: 'Provide notes so the requester can make corrections.',
            btnText: 'Send Revision', btnClass: 'px-3 py-1.5 text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition',
            iconBg: 'bg-amber-100 dark:bg-amber-900/40', iconColor: 'text-amber-600 dark:text-amber-400',
            iconPath: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        },
        rejected: {
            title: 'Reject Request', subtitle: 'Explain the reason for rejection so the requester has clarity.',
            btnText: 'Reject Request', btnClass: 'px-3 py-1.5 text-sm font-medium rounded-md text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition',
            iconBg: 'bg-red-100 dark:bg-red-900/40', iconColor: 'text-red-600 dark:text-red-400',
            iconPath: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        },
    };

    const modal = document.getElementById('actionModal');
    const titleEl = document.getElementById('modalTitle');
    const subtitleEl = document.getElementById('modalSubtitle');
    const statusInput = document.getElementById('modalStatusInput');
    const submitBtn = document.getElementById('modalSubmitBtn');
    const notesInput = document.getElementById('review_notes');
    const iconWrap = document.getElementById('modalIconWrap');

    function openActionModal(action) {
        const cfg = MODAL_CONFIG[action];
        if (!cfg || !modal) return;

        titleEl.textContent = cfg.title;
        subtitleEl.textContent = cfg.subtitle;
        statusInput.value = action;
        submitBtn.textContent = cfg.btnText;
        submitBtn.className = cfg.btnClass;

        iconWrap.className = `flex h-9 w-9 items-center justify-center rounded-full shrink-0 ${cfg.iconBg} ${cfg.iconColor}`;
        iconWrap.innerHTML = `<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="${cfg.iconPath}"/></svg>`;

        notesInput.value = '';
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(() => notesInput?.focus(), 100);
    }

    function closeActionModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    window.openActionModal  = openActionModal;
    window.closeActionModal = closeActionModal;

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) closeActionModal();
    });

    // Fungsi untuk validasi TTD
    window.validateSignature = function(field, btnElement) {
        if (!confirm('Apakah Anda yakin ingin memvalidasi tanda tangan pada kolom ini?')) return;

        const originalText = btnElement.innerHTML;
        btnElement.disabled = true;
        btnElement.innerHTML = 'Processing...';

        fetch(`{{ route('procurement.signature.update', $procurementRequest->id) }}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ signature_field: field })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || data.error || 'Server Error');
            }
            return data;
        })
        .then(data => {
            if(data.success) {
                window.location.reload(); 
            } else {
                alert('Gagal: ' + (data.error || 'Terjadi kesalahan.'));
                btnElement.disabled = false;
                btnElement.innerHTML = originalText;
            }
        })
        .catch(err => {
            alert('Error: ' + err.message);
            btnElement.disabled = false;
            btnElement.innerHTML = originalText;
        });
    };
})();
</script>
@endpush
@endsection