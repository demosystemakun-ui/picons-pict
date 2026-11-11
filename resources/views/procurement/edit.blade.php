@extends('layouts.app')

@section('title', 'Edit Procurement Request')
@section('page-title', 'Edit Procurement Request')

@php
    $itemsForForm = $procurementRequest->items->isNotEmpty()
        ? $procurementRequest->items
        : collect([
            (object) [
                'item_name'             => $procurementRequest->item_name,
                'item_requirement_note' => $procurementRequest->item_requirement_note,
                'quantity'              => $procurementRequest->quantity,
                'unit'                  => null,
                'picture'               => null,
            ],
        ]);

    // Fixed order type detection logic
    $activeOrderType = null;
    if ($procurementRequest->is_new_order) $activeOrderType = 'new_order';
    elseif ($procurementRequest->is_repeat_order) $activeOrderType = 'repeat_order';
    elseif ($procurementRequest->is_goods) $activeOrderType = 'goods';
    elseif ($procurementRequest->is_services) $activeOrderType = 'services';
@endphp

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-24 pt-6 px-4">

    {{-- HEADER --}}
    <div class="flex items-start gap-3">
        <a href="{{ route('procurement.show', $procurementRequest) }}"
           class="mt-0.5 inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition shrink-0">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-gray-900 dark:text-white font-mono">
                Edit {{ $procurementRequest->no }}
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Update procurement request details</p>
        </div>
    </div>

    {{-- ERROR ALERT --}}
    @if ($errors->any())
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/60 px-5 py-4">
            <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">
                There are {{ $errors->count() }} errors in the form
            </h3>
            <ul class="mt-2 list-disc list-inside text-sm text-red-700 dark:text-red-300/90 space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('procurement.update', $procurementRequest) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- SECTION 1: GENERAL INFORMATION --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">General Information</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Basic details of the procurement request</p>
            </header>
            
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date" required
                               value="{{ old('date', optional($procurementRequest->date)->format('Y-m-d')) }}"
                               class="w-full h-10 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm px-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Department <span class="text-red-500">*</span></label>
                        <select name="department_id" required class="w-full h-10 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm px-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                            <option value="">— Select Department —</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $procurementRequest->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }} ({{ $dept->code ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Requested By <span class="text-red-500">*</span></label>
                        <input type="text" name="request_by" required value="{{ old('request_by', $procurementRequest->request_by) }}"
                               class="w-full h-10 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm px-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Prepared By <span class="text-red-500">*</span></label>
                        <input type="text" name="prepared_by" required value="{{ old('prepared_by', $procurementRequest->prepared_by) }}"
                               class="w-full h-10 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm px-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Procurement Description <span class="text-red-500">*</span></label>
                        <input type="text" name="description" required value="{{ old('description', $procurementRequest->description) }}"
                               class="w-full h-10 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm px-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">Order Type</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach(['new_order' => 'New Order', 'repeat_order' => 'Repeat Order', 'goods' => 'Goods', 'services' => 'Services'] as $key => $label)
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="radio" name="order_type" value="{{ $key }}"
                                           {{ old('order_type', $activeOrderType) === $key ? 'checked' : '' }}
                                           class="rounded-full border-gray-300 text-primary-600 focus:ring-primary-500">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">
                            Procurement Method <span class="text-red-500">*</span>
                        </label>
                        <div class="flex flex-wrap gap-4">
                            @foreach(['po' => 'Purchase Order (PO)', 'cash_advance' => 'Cash Advance', 'reimbursement' => 'Reimbursement'] as $key => $label)
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="radio" name="procurement_method" value="{{ $key }}"
                                           {{ old('procurement_method', $procurementRequest->procurement_method ?? 'po') === $key ? 'checked' : '' }}
                                           class="rounded-full border-gray-300 text-primary-600 focus:ring-primary-500" required>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 2: JUSTIFICATION --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Requirement Details &amp; Justification</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Reason and specifications requested</p>
            </header>
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Background</label>
                    <textarea name="background" rows="3"
                              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm p-3 resize-y focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">{{ old('background', $procurementRequest->background) }}</textarea>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Purpose</label>
                    <textarea name="purpose" rows="3"
                              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm p-3 resize-y focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">{{ old('purpose', $procurementRequest->purpose) }}</textarea>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Required Specification</label>
                    <textarea name="required_spec" rows="3"
                              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm p-3 resize-y focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">{{ old('required_spec', $procurementRequest->required_spec) }}</textarea>
                </div>

                {{-- ═══ FIELD BARU: Usulan Vendor ═══ --}}
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Usulan Vendor / Link Referensi (Opsional)</label>
                    <textarea name="vendor_suggestion" rows="3"
                              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm p-3 resize-y focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors"
                              placeholder="Contoh: Tokopedia (https://tokopedia.com/...), atau Monotaro">{{ old('vendor_suggestion', $procurementRequest->vendor_suggestion) }}</textarea>
                    <p class="mt-1 text-[11px] text-gray-500">Anda bisa mengusulkan nama toko atau menempelkan link produk yang Anda temukan sebagai referensi untuk Admin.</p>
                </div>
                {{-- ══════════════════════════════════ --}}
            </div>
        </section>

        {{-- SECTION 3: ITEMS --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Requested Items</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Requested goods or services — can be added/removed</p>
                </div>
                <button type="button" onclick="addItemRow()"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Add Item
                </button>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/50">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase w-10 text-center">No.</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase w-1/4">Item Name</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase">Requirement Note</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase w-20 text-center">Qty</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase w-20 text-center">Unit</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase w-40">Picture</th>
                            <th class="py-3 px-4 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($itemsForForm as $item)
                            <tr class="item-row align-top">
                                <td class="py-3 px-4 text-center"><span class="row-number text-sm font-medium text-gray-500"></span></td>
                                <td class="py-3 px-4">
                                    <textarea name="items[{{ $loop->index }}][item_name]" rows="2" required
                                              class="w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm p-2 resize-y focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">{{ $item->item_name }}</textarea>
                                </td>
                                <td class="py-3 px-4">
                                    <textarea name="items[{{ $loop->index }}][item_requirement_note]" rows="2"
                                              class="w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm p-2 resize-y focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">{{ $item->item_requirement_note }}</textarea>
                                </td>
                                <td class="py-3 px-4">
                                    <input type="number" step="0.01" name="items[{{ $loop->index }}][quantity]" value="{{ $item->quantity }}" min="0.01" required
                                           class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm px-2 text-center focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                                </td>
                                <td class="py-3 px-4">
                                    <input type="text" name="items[{{ $loop->index }}][unit]" value="{{ $item->unit ?? 'Pcs' }}"
                                           class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm px-2 text-center focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                                </td>
                                <td class="py-3 px-4">
                                    <input type="hidden" name="items[{{ $loop->index }}][existing_picture]" value="{{ $item->picture }}">
                                    @if($item->picture)
                                        <img src="{{ Storage::url($item->picture) }}" class="h-10 w-10 object-cover rounded-md border border-gray-200 dark:border-gray-600 mb-1.5">
                                    @endif
                                    <input type="file" name="items[{{ $loop->index }}][picture]" accept="image/*"
                                           class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:bg-gray-100 dark:file:bg-gray-700 dark:text-gray-300">
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button type="button" onclick="removeItemRow(this)"
                                            class="text-red-400 hover:text-red-600 p-1.5 rounded-md hover:bg-red-50 dark:hover:bg-red-900/30 inline-flex transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/30">
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Target Purchase</label>
                <textarea name="target_purchase" rows="2"
                          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm p-3 resize-y focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">{{ old('target_purchase', $procurementRequest->target_purchase) }}</textarea>
            </div>
        </section>

        {{-- SECTION 4: SIGNATURES --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Approval &amp; Signatures</h2>
            </header>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
                @foreach([
                    ['name' => 'pic_name', 'label' => 'PIC', 'value' => $procurementRequest->pic_name],
                    ['name' => 'ops_manager_name', 'label' => 'Ops Manager', 'value' => $procurementRequest->ops_manager_name],
                    ['name' => 'fem_manager_name', 'label' => 'FEM Manager', 'value' => $procurementRequest->fem_manager_name],
                    ['name' => 'coo_name', 'label' => 'COO', 'value' => $procurementRequest->coo_name],
                ] as $sig)
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">{{ $sig['label'] }}</label>
                        <input type="text" name="{{ $sig['name'] }}" value="{{ old($sig['name'], $sig['value']) }}"
                               class="w-full h-10 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm px-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ACTION BAR --}}
        <div class="sticky bottom-0 z-50 -mx-4 px-4 py-4 mt-6 bg-gradient-to-t from-slate-50 dark:from-gray-900 from-60%">
            <div class="flex items-center justify-between rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-4 shadow-lg">
                <p class="text-[13px] text-gray-500 dark:text-gray-400 hidden sm:block">
                    Make sure all data is correct before saving.
                </p>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('procurement.show', $procurementRequest) }}"
                       class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-emerald-600 hover:bg-emerald-700 px-6 py-2.5 text-sm font-medium text-white shadow-sm transition">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let itemIndex = {{ $itemsForForm->count() }};

    function updateRowNumbers() {
        document.querySelectorAll('.item-row').forEach((row, index) => {
            row.querySelector('.row-number').textContent = index + 1;
        });
    }

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.className = 'item-row align-top';
        
        tr.innerHTML = `
            <td class="py-3 px-4 text-center"><span class="row-number text-sm font-medium text-gray-500"></span></td>
            <td class="py-3 px-4">
                <textarea name="items[\${itemIndex}][item_name]" rows="2" required
                          class="w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm p-2 resize-y focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors"></textarea>
            </td>
            <td class="py-3 px-4">
                <textarea name="items[\${itemIndex}][item_requirement_note]" rows="2"
                          class="w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm p-2 resize-y focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors"></textarea>
            </td>
            <td class="py-3 px-4">
                <input type="number" step="0.01" name="items[\${itemIndex}][quantity]" value="1" min="0.01" required
                       class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm px-2 text-center focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
            </td>
            <td class="py-3 px-4">
                <input type="text" name="items[\${itemIndex}][unit]" value="Pcs"
                       class="w-full h-9 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm px-2 text-center focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
            </td>
            <td class="py-3 px-4">
                <input type="hidden" name="items[\${itemIndex}][existing_picture]" value="">
                <input type="file" name="items[\${itemIndex}][picture]" accept="image/*"
                       class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:bg-gray-100 dark:file:bg-gray-700 dark:text-gray-300">
            </td>
            <td class="py-3 px-4 text-center">
                <button type="button" onclick="removeItemRow(this)"
                        class="text-red-400 hover:text-red-600 p-1.5 rounded-md hover:bg-red-50 dark:hover:bg-red-900/30 inline-flex transition-colors">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        itemIndex++;
        updateRowNumbers();
        if (window.lucide) lucide.createIcons();
    }

    function removeItemRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('There must be at least 1 item.');
            return;
        }
        btn.closest('tr').remove();
        updateRowNumbers();
    }

    document.addEventListener('DOMContentLoaded', updateRowNumbers);
</script>
@endpush
@endsection