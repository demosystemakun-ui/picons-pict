@extends('layouts.app')

@section('title', 'Consumable Request Detail')
@section('page-title', 'Consumable Request Detail')

@section('content')
<div class="max-w-6xl mx-auto pb-24" x-data="{ 
    modalOpen: false, 
    modalImg: '',
    openItems: [0] // item pertama terbuka default
}">

    @if (session('success'))
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- STICKY HEADER --}}
    <div class="sticky top-0 z-30 -mx-4 sm:mx-0 mb-4 bg-white/95 dark:bg-gray-800/95 backdrop-blur border-b border-gray-200 dark:border-gray-700">
        <div class="px-4 sm:px-0 py-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ auth()->user()->hasRole('Super Admin') ? route('consumable-requests.index') : route('consumable-requests.my-index') }}"
                   class="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                            #{{ $consumableRequest->id }} — {{ $consumableRequest->subject }}
                        </h1>
                        @php
                            $statusMap = [
                                'approved' => ['Approved', 'bg-emerald-100 text-emerald-700 border-emerald-200'],
                                'rejected' => ['Rejected', 'bg-red-100 text-red-700 border-red-200'],
                                'revision' => ['Revision', 'bg-amber-100 text-amber-700 border-amber-200'],
                                'converted'=> ['Converted to PR', 'bg-blue-100 text-blue-700 border-blue-200'],
                                'pending'  => ['Pending', 'bg-gray-100 text-gray-700 border-gray-200'],
                            ];
                            [$statusLabel, $statusClass] = $statusMap[$consumableRequest->status] ?? [ucfirst($consumableRequest->status), 'bg-gray-100 text-gray-700 border-gray-200'];
                        @endphp
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded border {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ \Carbon\Carbon::parse($consumableRequest->date)->format('d M Y') }}
                        · {{ $consumableRequest->items->count() }} item
                        · Request by {{ $consumableRequest->request_by ?: '-' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('consumable-requests.print', $consumableRequest->id) }}" target="_blank"
                   class="px-2.5 py-1.5 rounded-md border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs">
                    Print
                </a>
                <a href="{{ route('consumable-requests.pdf', $consumableRequest->id) }}"
                   class="px-2.5 py-1.5 rounded-md border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs">
                    PDF
                </a>

                @if(auth()->user()->hasRole('Super Admin') || ($consumableRequest->user_id === auth()->id() && $consumableRequest->status == 'pending'))
                    <a href="{{ route('consumable-requests.edit', $consumableRequest->id) }}"
                       class="px-2.5 py-1.5 rounded-md bg-amber-500 hover:bg-amber-600 text-white text-xs">
                        Edit
                    </a>
                @endif

                @if(auth()->user()->hasRole(['Super Admin', 'Manager', 'Approver']) && $consumableRequest->status === 'pending')
                    <form action="{{ route('consumable-requests.update-status', $consumableRequest) }}" method="POST" class="inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="approved">
                        <button class="px-2.5 py-1.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-xs">Approve</button>
                    </form>
                    <form action="{{ route('consumable-requests.update-status', $consumableRequest) }}" method="POST" class="inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="revision">
                        <button class="px-2.5 py-1.5 rounded-md bg-orange-500 hover:bg-orange-600 text-white text-xs">Revision</button>
                    </form>
                    <form action="{{ route('consumable-requests.update-status', $consumableRequest) }}" method="POST" class="inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="rejected">
                        <button class="px-2.5 py-1.5 rounded-md bg-red-600 hover:bg-red-700 text-white text-xs">Reject</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- MAIN GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        {{-- LEFT: META INFO --}}
        <aside class="lg:col-span-3 space-y-3">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h3 class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 mb-3">Informasi Dokumen</h3>
                <dl class="space-y-2.5 text-xs">
                    <div>
                        <dt class="text-gray-400">Document ID</dt>
                        <dd class="font-medium text-gray-800 dark:text-gray-200">#{{ $consumableRequest->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Tanggal Request</dt>
                        <dd class="font-medium text-gray-800 dark:text-gray-200">{{ \Carbon\Carbon::parse($consumableRequest->date)->format('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Request By</dt>
                        <dd class="font-medium text-gray-800 dark:text-gray-200">{{ $consumableRequest->request_by ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Approved By</dt>
                        <dd class="font-medium text-gray-800 dark:text-gray-200">{{ $consumableRequest->approved_by ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Verified By</dt>
                        <dd class="font-medium text-gray-800 dark:text-gray-200">{{ $consumableRequest->verified_by ?: '-' }}</dd>
                    </div>
                </dl>
            </div>

            @if($consumableRequest->general_note)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <h3 class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 mb-2">Catatan Umum</h3>
                    <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $consumableRequest->general_note }}</p>
                </div>
            @endif
        </aside>

        {{-- RIGHT: ITEMS --}}
        <section class="lg:col-span-9">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">

                {{-- TABLE HEADER (summary row) --}}
                <div class="hidden sm:grid grid-cols-12 gap-2 px-4 py-2 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700 text-[10px] font-semibold uppercase tracking-wider text-gray-500">
                    <div class="col-span-1">#</div>
                    <div class="col-span-6">Item</div>
                    <div class="col-span-2">Qty</div>
                    <div class="col-span-2">Attachment</div>
                    <div class="col-span-1"></div>
                </div>

                @foreach($consumableRequest->items as $index => $item)
                    @php
                        $detailLines = [];
                        if (!empty($item->item_name)) $detailLines[] = $item->item_name;
                        if (!empty($item->brand))     $detailLines[] = 'Brand: ' . $item->brand;
                        if (!empty($item->type))      $detailLines[] = 'Type: ' . $item->type;
                        if (!empty($item->model))     $detailLines[] = 'Model: ' . $item->model;
                        if (!empty($item->capacity))  $detailLines[] = 'Capacity: ' . $item->capacity;
                        if (!empty($item->specs))     $detailLines[] = 'Spec: ' . $item->specs;
                        if (empty($detailLines) && !empty($item->item_details)) $detailLines[] = $item->item_details;
                        $summaryTitle = $item->item_name ?: ($detailLines[0] ?? 'Item tanpa nama');
                    @endphp

                    <div class="border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                         x-data="{ open: @js($index === 0) }">

                        {{-- Row ringkasan --}}
                        <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center hover:bg-gray-50 dark:hover:bg-gray-900/30">
                            <div class="col-span-1 text-xs font-semibold text-gray-400">{{ $index + 1 }}</div>
                            <div class="col-span-7 sm:col-span-6 min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $summaryTitle }}</p>
                                @if(count($detailLines) > 1)
                                    <p class="text-[11px] text-gray-500 truncate mt-0.5">{{ implode(' · ', array_slice($detailLines, 1)) }}</p>
                                @endif
                            </div>
                            <div class="col-span-2 text-xs text-gray-700 dark:text-gray-300">
                                {{ intval($item->quantity) }} <span class="text-gray-400">{{ $item->unit }}</span>
                            </div>
                            <div class="col-span-1 sm:col-span-2">
                                @if($item->picture)
                                    @php $imageUrl = Storage::disk('supabase-consumable_request')->temporaryUrl($item->picture, now()->addMinutes(60)); @endphp
                                    <button type="button"
                                            @click.stop="modalOpen = true; modalImg = '{{ $imageUrl }}'"
                                            class="inline-flex items-center gap-1 text-[11px] text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Lihat
                                    </button>
                                @else
                                    <span class="text-[11px] text-gray-400">-</span>
                                @endif
                            </div>
                            <div class="col-span-1 text-right">
                                <button @click="open = !open"
                                        class="p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500"
                                        :aria-expanded="open">
                                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Detail expandable --}}
                        <div x-show="open" x-collapse class="px-4 pb-4 bg-gray-50/50 dark:bg-gray-900/20">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">1. Alasan Pembelian</p>
                                    <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $item->purpose_1 ?: '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">2. Proses / Aktivitas</p>
                                    <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $item->purpose_2 ?: '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">3. Efisiensi Operasional</p>
                                    <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $item->purpose_3 ?: '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- LIGHTBOX --}}
    <div x-show="modalOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
         @keydown.escape.window="modalOpen = false">
        <div @click.outside="modalOpen = false" class="relative max-w-4xl max-h-[90vh]">
            <button @click="modalOpen = false"
                    class="absolute -top-10 right-0 p-1.5 rounded-md bg-white/10 hover:bg-white/20 text-white">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img :src="modalImg" alt="Preview" class="max-h-[85vh] max-w-full object-contain rounded">
        </div>
    </div>

</div>
@include('consumable-requests._revision-modal')
@endsection