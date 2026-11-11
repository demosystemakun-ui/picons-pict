@extends('layouts.app')

@section('title', 'Create Procurement Request')
@section('page-title', 'New Procurement Request')

@section('content')
<div class="max-w-5xl mx-auto pb-24">

    {{-- PAGE HEADER --}}
    <div class="flex items-start justify-between gap-4 mb-5">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Procurement Request
                </h1>
                <span class="text-[11px] font-medium px-2 py-0.5 rounded border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                    {{ isset($prefill) ? 'Merged Draft' : 'New Draft' }}
                </span>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Submit a procurement request for goods or services with complete and structured details.
            </p>
        </div>
    </div>

    {{-- PREFILL NOTICE --}}
    @if(isset($prefill))
        <div class="mb-4 flex items-start gap-2.5 rounded-md border border-blue-200 dark:border-blue-800/60 bg-blue-50 dark:bg-blue-900/20 px-3.5 py-2.5">
            <svg class="h-4 w-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-xs">
                <p class="font-semibold text-blue-800 dark:text-blue-300">Data loaded automatically</p>
                <p class="text-blue-700 dark:text-blue-400 mt-0.5">
                    This form was prefilled from a <strong>Consolidated Consumable Request</strong>. Review the items and details before saving.
                </p>
            </div>
        </div>
    @endif

    {{-- ERROR ALERT --}}
    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 dark:border-red-800/60 bg-red-50 dark:bg-red-900/20 px-3.5 py-2.5">
            <div class="flex items-start gap-2.5">
                <svg class="h-4 w-4 flex-shrink-0 text-red-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                </svg>
                <div class="text-xs">
                    <p class="font-semibold text-red-800 dark:text-red-300">{{ $errors->count() }} validation {{ $errors->count() === 1 ? 'error' : 'errors' }}</p>
                    <ul class="mt-1 list-disc list-inside space-y-0.5 text-red-700 dark:text-red-300/90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('procurement.store') }}" class="space-y-6">
        @csrf

        @if(isset($prefill['request_ids']))
            @foreach($prefill['request_ids'] as $id)
                <input type="hidden" name="consumable_request_ids[]" value="{{ $id }}">
            @endforeach
        @endif

        {{-- =========================================================
             SECTION 1 — GENERAL INFORMATION
        ========================================================== --}}
        <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    General Information
                </h2>
            </div>

            <div class="p-5 space-y-4">
                {{-- Row 1: no + date --}}
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    <div class="sm:col-span-7">
                        <label for="no" class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                            Reference No.
                            @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        
                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                            <input type="text" id="no" name="no"
                                   value="{{ old('no', $prefill['no'] ?? $suggestedNo) }}" required
                                   class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none">
                        @else
                            <input type="text" value="AUTO-GENERATED" disabled
                                   class="w-full h-9 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-sm px-3 cursor-not-allowed">
                            <p class="mt-1 text-[11px] text-gray-500">Nomor PR akan diisi oleh Admin saat proses review.</p>
                        @endif
                    </div>
                    <div class="sm:col-span-5">
                        <label for="date" class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="date" name="date"
                               value="{{ old('date', date('Y-m-d')) }}" required
                               class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none">
                    </div>
                </div>

                {{-- Row 2: request_by + department + prepared_by --}}
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    <div class="sm:col-span-4">
                        <label for="request_by" class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                            Request By <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="request_by" name="request_by"
                               value="{{ old('request_by', auth()->user()->name) }}" required
                               class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none">
                    </div>
                    <div class="sm:col-span-4">
                        <label for="department_id" class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                            Division / Department <span class="text-red-500">*</span>
                        </label>
                        <select name="department_id" id="department_id" required
                                class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-2.5 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none @error('department_id') border-red-500 @enderror">
                            <option value="" disabled {{ old('department_id', auth()->user()->department_id ?? '') ? '' : 'selected' }}>— Select Department —</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', auth()->user()->department_id ?? '') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }} ({{ $dept->code ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-4">
                        <label for="prepared_by" class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                            Prepared By <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="prepared_by" name="prepared_by"
                               value="{{ old('prepared_by', auth()->user()->name) }}" required
                               class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none">
                    </div>
                </div>

                {{-- Row 3: description --}}
                <div>
                    <label for="description" class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="description" name="description"
                           value="{{ old('description', isset($prefill) ? 'Consolidation of Consumable Requests' : '') }}" required
                           placeholder="A short summary of this procurement request"
                           class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none">
                </div>

                {{-- Row 4: order type + procurement method (side by side) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                            Order Type <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ([
                                'new_order'    => 'New Order',
                                'repeat_order' => 'Repeat Order',
                                'goods'        => 'Goods',
                                'services'     => 'Services',
                            ] as $value => $text)
                                <label class="flex cursor-pointer items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-2 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 transition hover:border-primary-400 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:text-primary-700 dark:has-[:checked]:bg-primary-900/30 dark:has-[:checked]:text-primary-300">
                                    <input type="radio" name="order_type" value="{{ $value }}"
                                           {{ old('order_type', 'goods') === $value ? 'checked' : '' }}
                                           class="sr-only">
                                    {{ $text }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                            Procurement Method <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 gap-2">
                            @foreach ([
                                'po'            => 'Purchase Order (PO)',
                                'cash_advance'  => 'Cash Advance',
                                'reimbursement' => 'Reimbursement',
                            ] as $value => $text)
                                <label class="flex cursor-pointer items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 transition hover:border-primary-400 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:text-primary-700 dark:has-[:checked]:bg-primary-900/30 dark:has-[:checked]:text-primary-300">
                                    <input type="radio" name="procurement_method" value="{{ $value }}"
                                           {{ old('procurement_method', 'po') === $value ? 'checked' : '' }}
                                           class="sr-only">
                                    {{ $text }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
             SECTION 2 — JUSTIFICATION
        ========================================================== --}}
        <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Justification
                </h2>
            </div>

            <div class="p-5 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="background" class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                            Background
                        </label>
                        <textarea id="background" name="background" rows="3"
                                  placeholder="Why do you want to purchase this?"
                                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 resize-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none">{{ old('background', $prefill['background'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label for="purpose" class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                            Purpose
                        </label>
                        <textarea id="purpose" name="purpose" rows="3"
                                  placeholder="What is the impact? What happens if not purchased?"
                                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 resize-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none">{{ old('purpose', $prefill['purpose'] ?? '') }}</textarea>
                    </div>
                </div>

                <div>
                    <label for="required_spec" class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                        Required Spec
                    </label>
                    <textarea id="required_spec" name="required_spec" rows="3"
                              placeholder="Detailed specification and the reason behind it"
                              class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 resize-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none">{{ old('required_spec', $prefill['required_spec'] ?? '') }}</textarea>
                </div>

                {{-- ═══ FIELD BARU: Usulan Vendor ═══ --}}
                <div>
                    <label for="vendor_suggestion" class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                        Usulan Vendor / Link Referensi (Opsional)
                    </label>
                    <textarea id="vendor_suggestion" name="vendor_suggestion" rows="3"
                              placeholder="Contoh: Tokopedia (https://tokopedia.com/...), atau Monotaro"
                              class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 resize-y focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none">{{ old('vendor_suggestion') }}</textarea>
                    <p class="mt-1 text-[11px] text-gray-500">Anda bisa mengusulkan nama toko atau menempelkan link produk yang Anda temukan sebagai referensi untuk Admin.</p>
                </div>
                {{-- ══════════════════════════════════ --}}
            </div>
        </section>

        {{-- =========================================================
             SECTION 3 — ITEM REQUEST
        ========================================================== --}}
        <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Item Request
                </h2>
                @if(isset($prefill['items']) && count($prefill['items']) > 0)
                    <span class="text-[11px] text-gray-500">
                        {{ count($prefill['items']) }} {{ count($prefill['items']) === 1 ? 'item' : 'items' }} from Consumable Request
                    </span>
                @endif
            </div>

            <div class="p-5 space-y-4">

                {{-- CASE 1: FROM MERGED CONSUMABLE REQUEST --}}
                @if(isset($prefill['items']) && count($prefill['items']) > 0)
                    <div class="space-y-2">
                        @foreach($prefill['items'] as $index => $item)
                            <div class="rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/30 p-3">
                                <div class="grid grid-cols-12 gap-2">
                                    <div class="col-span-12 sm:col-span-5">
                                        <label class="block text-[10px] font-medium text-gray-500 dark:text-gray-400 mb-1">Item Name</label>
                                        <input type="text" name="items[{{ $index }}][item_name]" value="{{ $item['item_name'] }}" required
                                               class="w-full h-8 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm px-2.5">
                                    </div>
                                    <div class="col-span-4 sm:col-span-1">
                                        <label class="block text-[10px] font-medium text-gray-500 dark:text-gray-400 mb-1">Qty</label>
                                        <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}" min="0.01" step="any" required
                                               class="w-full h-8 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm px-2.5">
                                    </div>
                                    <div class="col-span-4 sm:col-span-1">
                                        <label class="block text-[10px] font-medium text-gray-500 dark:text-gray-400 mb-1">Unit</label>
                                        <input type="text" name="items[{{ $index }}][unit]" value="{{ $item['unit'] ?? 'Pcs' }}"
                                               class="w-full h-8 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm px-2.5">
                                    </div>
                                    <div class="col-span-12 sm:col-span-5">
                                        <label class="block text-[10px] font-medium text-gray-500 dark:text-gray-400 mb-1">Requirement / Spec</label>
                                        <input type="text" name="items[{{ $index }}][item_requirement_note]" value="{{ $item['item_requirement_note'] ?? '' }}"
                                               class="w-full h-8 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm px-2.5">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- CASE 2: MANUAL INPUT (BISA TAMBAH BARIS) --}}
                    <div id="manualItemContainer" class="space-y-3">
                        {{-- Baris Pertama --}}
                        <div class="item-row grid grid-cols-12 gap-3 items-end p-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/30">
                            <div class="col-span-12 sm:col-span-5">
                                <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">Requested Item <span class="text-red-500">*</span></label>
                                <input type="text" name="items[0][item_name]" required class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm px-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">Match Requirement?</label>
                                <input type="text" name="items[0][item_requirement_note]" class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm px-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            </div>
                            <div class="col-span-4 sm:col-span-1">
                                <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">Qty <span class="text-red-500">*</span></label>
                                <input type="number" min="1" name="items[0][quantity]" value="1" required class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm px-2 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            </div>
                            <div class="col-span-4 sm:col-span-1">
                                <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">Unit</label>
                                <input type="text" name="items[0][unit]" value="Pcs" class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm px-2 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            </div>
                            <div class="col-span-4 sm:col-span-1 flex justify-center">
                                {{-- Placeholder untuk tombol hapus --}}
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="button" onclick="addManualItem()" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Add Another Item
                        </button>
                    </div>
                @endif

                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                    <label for="target_purchase" class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                        Target to Purchase
                    </label>
                    <textarea id="target_purchase" name="target_purchase" rows="2"
                              placeholder="Consider the installation schedule"
                              class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 py-2 resize-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none">{{ old('target_purchase') }}</textarea>
                </div>
            </div>
        </section>

        {{-- =========================================================
             SECTION 4 — APPROVAL CHAIN
        ========================================================== --}}
        <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Approval Chain
                </h2>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ([
                        ['name' => 'pic_name',         'label' => 'PIC'],
                        ['name' => 'ops_manager_name', 'label' => 'Ops Manager'],
                        ['name' => 'fem_manager_name', 'label' => 'FEM Manager'],
                        ['name' => 'coo_name',         'label' => 'COO'],
                    ] as $field)
                        <div>
                            <label for="{{ $field['name'] }}" class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                {{ $field['label'] }}
                            </label>
                            <input type="text" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ old($field['name']) }}"
                                   class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm px-3 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none">
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- STICKY ACTION BAR --}}
        <div class="sticky bottom-4 z-10">
            <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white/95 dark:bg-gray-800/95 backdrop-blur px-4 py-2.5 shadow-lg">
                <p class="hidden sm:block text-xs text-gray-500 dark:text-gray-400">
                    Please verify all data is correct before saving.
                </p>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <a href="{{ url()->previous() }}"
                       class="flex-1 sm:flex-none inline-flex items-center justify-center h-9 px-3.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 h-9 px-4 rounded-md bg-primary-600 hover:bg-primary-700 text-sm font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Request
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let manualItemIndex = 1;

    function addManualItem() {
        const container = document.getElementById('manualItemContainer');
        const newRow = document.createElement('div');
        newRow.className = 'item-row grid grid-cols-12 gap-3 items-end p-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/30 mt-3';
        
        newRow.innerHTML = `
            <div class="col-span-12 sm:col-span-5">
                <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">Requested Item <span class="text-red-500">*</span></label>
                <input type="text" name="items[\${manualItemIndex}][item_name]" required class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm px-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
            <div class="col-span-12 sm:col-span-4">
                <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">Match Requirement?</label>
                <input type="text" name="items[\${manualItemIndex}][item_requirement_note]" class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm px-3 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
            <div class="col-span-4 sm:col-span-1">
                <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">Qty <span class="text-red-500">*</span></label>
                <input type="number" min="1" name="items[\${manualItemIndex}][quantity]" value="1" required class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm px-2 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
            <div class="col-span-4 sm:col-span-1">
                <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1.5">Unit</label>
                <input type="text" name="items[\${manualItemIndex}][unit]" value="Pcs" class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm px-2 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
            <div class="col-span-4 sm:col-span-1 flex justify-center pb-1">
                <button type="button" onclick="this.closest('.item-row').remove()" class="p-2 text-rose-500 hover:bg-rose-50 rounded-md transition" title="Hapus Baris">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
        `;
        
        container.appendChild(newRow);
        manualItemIndex++;
    }
</script>
@endpush