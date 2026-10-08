@extends('layouts.app')

@section('title', 'Detail Consumable Request')
@section('page-title', 'Detail Consumable Request')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-24" x-data="{ modalOpen: false, modalImg: '' }">

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header Action Buttons --}}
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full 
                @if($consumableRequest->status == 'approved') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300
                @elseif($consumableRequest->status == 'rejected') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                @elseif($consumableRequest->status == 'revision') bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300
                @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                Status: {{ ucfirst($consumableRequest->status) }}
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Tombol Cetak & PDF --}}
            <a href="{{ route('consumable-requests.print', $consumableRequest->id) }}" target="_blank"
               class="px-3.5 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-medium">
                Print
            </a>
            <a href="{{ route('consumable-requests.pdf', $consumableRequest->id) }}"
               class="px-3.5 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-medium">
                Unduh PDF
            </a>

            {{-- Tombol Edit --}}
            @if(auth()->user()->hasRole('Super Admin') || ($consumableRequest->user_id === auth()->id() && $consumableRequest->status == 'pending'))
                <a href="{{ route('consumable-requests.edit', $consumableRequest->id) }}"
                   class="px-3.5 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium">
                    Edit
                </a>
            @endif

            {{-- Tombol Approval Admin/Manager --}}
            @if(auth()->user()->hasRole(['Super Admin', 'Manager', 'Approver']) && $consumableRequest->status === 'pending')
                <form action="{{ route('consumable-requests.update-status', $consumableRequest) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium">
                        Approve
                    </button>
                </form>

                <form action="{{ route('consumable-requests.update-status', $consumableRequest) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="revision">
                    <button type="submit" class="px-3.5 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-xs font-medium">
                        Minta Revisi
                    </button>
                </form>

                <form action="{{ route('consumable-requests.update-status', $consumableRequest) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="px-3.5 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-medium">
                        Reject
                    </button>
                </form>
            @endif

            <a href="{{ auth()->user()->hasRole('Super Admin') ? route('consumable-requests.index') : route('consumable-requests.my-index') }}"
               class="px-3.5 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-medium">
                Kembali
            </a>
        </div>
    </div>

    {{-- Main Document Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm space-y-6 p-6">

        {{-- Header Info --}}
        <div class="grid sm:grid-cols-2 gap-4 pb-6 border-b border-gray-100 dark:border-gray-700">
            <div>
                <span class="text-xs text-gray-400 block">ID Dokumen</span>
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">#{{ $consumableRequest->id }}</span>
            </div>
            <div>
                <span class="text-xs text-gray-400 block">Tanggal Pengajuan</span>
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ \Carbon\Carbon::parse($consumableRequest->date)->format('d F Y') }}</span>
            </div>
        </div>

        <div class="pb-6 border-b border-gray-100 dark:border-gray-700">
            <span class="text-xs text-gray-400 block mb-1">Subject</span>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $consumableRequest->subject }}</p>
        </div>

        {{-- Daftar Item Request --}}
        <div class="pb-6 border-b border-gray-100 dark:border-gray-700 space-y-4">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Daftar Item Request</h3>

            <div class="space-y-4">
                @foreach($consumableRequest->items as $index => $item)
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700 space-y-3">

                        {{-- Header Item --}}
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                            <span class="text-xs font-bold text-gray-500">Item #{{ $index + 1 }}</span>
                            <span class="text-xs font-semibold px-2 py-0.5 bg-primary-50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 rounded">
                                Qty: {{ intval($item->quantity) }} {{ $item->unit }}
                            </span>
                        </div>

                        {{-- Item Details --}}
                        <div>
                            <span class="text-[11px] font-semibold text-gray-400 uppercase block mb-1">Item Details / Specs</span>
                            <div class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line mt-0.5">
                                @php
                                    // Bangun daftar detail dari field baru, fallback ke item_details lama
                                    $detailLines = [];

                                    if (!empty($item->item_name)) $detailLines[] = 'Item: ' . $item->item_name;
                                    if (!empty($item->brand))     $detailLines[] = 'Brand: ' . $item->brand;
                                    if (!empty($item->type))      $detailLines[] = 'Type: ' . $item->type;
                                    if (!empty($item->model))     $detailLines[] = 'Model: ' . $item->model;
                                    if (!empty($item->capacity))  $detailLines[] = 'Capacity: ' . $item->capacity;
                                    if (!empty($item->specs))     $detailLines[] = $item->specs;

                                    // Fallback ke legacy item_details
                                    if (empty($detailLines) && !empty($item->item_details)) {
                                        $detailLines[] = $item->item_details;
                                    }
                                @endphp

                                @if(count($detailLines))
                                    {!! nl2br(e(implode("\n", $detailLines))) !!}
                                @else
                                    <span class="text-gray-400 italic">-</span>
                                @endif
                            </div>
                        </div>

                        {{-- Purpose 1, 2, 3 --}}
                        <div class="grid sm:grid-cols-3 gap-3 text-xs bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                            <div>
                                <span class="font-semibold text-gray-500 block mb-1">1. Why you want to purchase it?</span>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $item->purpose_1 }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-500 block mb-1">2. Which process/activity will it support?</span>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $item->purpose_2 }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-500 block mb-1">3. What operational/financial efficiency?</span>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $item->purpose_3 }}</p>
                            </div>
                        </div>

                        {{-- Picture --}}
                        @if($item->picture)
                            <div>
                                <span class="text-[11px] font-semibold text-gray-400 uppercase block mb-1">Picture Attachment</span>
                                <button type="button"
                                        @click="modalOpen = true; modalImg = '{{ Storage::url($item->picture) }}'"
                                        class="group relative block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 focus:outline-none">
                                    <img src="{{ Storage::url($item->picture) }}" alt="Item Picture"
                                         class="h-20 w-20 object-cover group-hover:scale-105 transition duration-200">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition duration-200">
                                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Note --}}
        @if($consumableRequest->general_note)
            <div class="pb-6 border-b border-gray-100 dark:border-gray-700">
                <span class="text-xs text-gray-400 block mb-1">Note</span>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $consumableRequest->general_note }}</p>
            </div>
        @endif

        {{-- Signatures --}}
        <div>
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Signatures</h3>
            <div class="grid sm:grid-cols-3 gap-4 text-center">
                <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-100 dark:border-gray-700">
                    <span class="text-xs text-gray-400 block mb-2 font-semibold">Request By</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $consumableRequest->request_by ?: '-' }}</span>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-100 dark:border-gray-700">
                    <span class="text-xs text-gray-400 block mb-2 font-semibold">Approved By</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $consumableRequest->approved_by ?: '-' }}</span>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-100 dark:border-gray-700">
                    <span class="text-xs text-gray-400 block mb-2 font-semibold">Verified By</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $consumableRequest->verified_by ?: '-' }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Lightbox Modal Preview --}}
    <div x-show="modalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
         style="display: none;"
         @keydown.escape.window="modalOpen = false">

        <div @click.outside="modalOpen = false" class="relative max-w-4xl max-h-[90vh] bg-white dark:bg-gray-900 rounded-xl overflow-hidden shadow-2xl p-2">
            <button @click="modalOpen = false"
                    class="absolute top-4 right-4 z-10 p-2 rounded-full bg-black/60 text-white hover:bg-black transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img :src="modalImg" alt="Enlarged Preview" class="max-h-[80vh] max-w-full object-contain rounded-lg mx-auto">
        </div>
    </div>

</div>
@endsection