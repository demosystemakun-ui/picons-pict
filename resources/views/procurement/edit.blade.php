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

    $activeOrderType = collect([
        'new_order'    => $procurementRequest->is_new_order,
        'repeat_order' => $procurementRequest->is_repeat_order,
        'goods'        => $procurementRequest->is_goods,
        'services'     => $procurementRequest->is_services,
    ])->search(fn ($v) => (bool) $v) ?: null;
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
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Perbarui detail pengajuan procurement</p>
        </div>
    </div>

    {{-- ERROR ALERT --}}
    @if ($errors->any())
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/60 px-5 py-4">
            <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">
                Terdapat {{ $errors->count() }} kesalahan pada form
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

        {{-- SECTION 1: INFORMASI UMUM --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Informasi Umum</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Detail dasar pengajuan procurement</p>
            </header>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="date" required
                           value="{{ old('date', optional($procurementRequest->date)->format('Y-m-d')) }}"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2.5">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">Departemen</label>
                    <select name="department_id" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2.5">
                        <option value="">— Pilih Departemen —</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $procurementRequest->department_id) == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">Diajukan Oleh <span class="text-red-500">*</span></label>
                    <input type="text" name="request_by" required value="{{ old('request_by', $procurementRequest->request_by) }}"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2.5">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">Divisi <span class="text-red-500">*</span></label>
                    <input type="text" name="division" required value="{{ old('division', $procurementRequest->division) }}"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2.5">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">Disiapkan Oleh <span class="text-red-500">*</span></label>
                    <input type="text" name="prepared_by" required value="{{ old('prepared_by', $procurementRequest->prepared_by) }}"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2.5">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">Deskripsi Pengadaan <span class="text-red-500">*</span></label>
                    <input type="text" name="description" required value="{{ old('description', $procurementRequest->description) }}"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2.5">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">Tipe Pesanan</label>
                    <div class="flex flex-wrap gap-4">
                        @foreach(['new_order' => 'New Order', 'repeat_order' => 'Repeat Order', 'goods' => 'Goods', 'services' => 'Services'] as $key => $label)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="radio" name="order_type" value="{{ $key }}"
                                       {{ old('order_type', $activeOrderType) === $key ? 'checked' : '' }}
                                       class="rounded-full border-gray-300 text-primary-600 focus:ring-primary-500">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 2: JUSTIFIKASI --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Detail Kebutuhan &amp; Justifikasi</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Alasan dan spesifikasi yang diminta</p>
            </header>
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">Background</label>
                    <textarea name="background" rows="3"
                              class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2.5 resize-y">{{ old('background', $procurementRequest->background) }}</textarea>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">Purpose</label>
                    <textarea name="purpose" rows="3"
                              class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2.5 resize-y">{{ old('purpose', $procurementRequest->purpose) }}</textarea>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">Required Specification</label>
                    <textarea name="required_spec" rows="3"
                              class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2.5 resize-y">{{ old('required_spec', $procurementRequest->required_spec) }}</textarea>
                </div>
            </div>
        </section>

        {{-- SECTION 3: ITEM --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Item Permintaan</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Barang atau jasa yang diajukan — bisa ditambah/dihapus</p>
                </div>
                <button type="button" onclick="addItemRow()"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah Item
                </button>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase w-10">No.</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase w-1/4">Nama Item</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase">Catatan Kesesuaian</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase w-24">Qty</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase w-24">Unit</th>
                            <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase w-40">Gambar</th>
                            <th class="py-3 px-4 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($itemsForForm as $item)
                            <tr class="item-row align-top">
                                <td class="py-4 px-4"><span class="row-number text-sm font-medium text-gray-500"></span></td>
                                <td class="py-4 px-4">
                                    <textarea name="items[{{ $loop->index }}][item_name]" rows="2" required
                                              class="w-full rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2 resize-y">{{ $item->item_name }}</textarea>
                                </td>
                                <td class="py-4 px-4">
                                    <textarea name="items[{{ $loop->index }}][item_requirement_note]" rows="2"
                                              class="w-full rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2 resize-y">{{ $item->item_requirement_note }}</textarea>
                                </td>
                                <td class="py-4 px-4">
                                    <input type="number" step="0.01" name="items[{{ $loop->index }}][quantity]" value="{{ $item->quantity }}" min="0.01" required
                                           class="w-full border-0 border-b border-gray-200 dark:border-gray-700 dark:bg-transparent dark:text-white text-sm px-1 py-2 text-center focus:ring-0 focus:border-primary-500">
                                </td>
                                <td class="py-4 px-4">
                                    <input type="text" name="items[{{ $loop->index }}][unit]" value="{{ $item->unit ?? 'Pcs' }}"
                                           class="w-full border-0 border-b border-gray-200 dark:border-gray-700 dark:bg-transparent dark:text-white text-sm px-1 py-2 text-center focus:ring-0 focus:border-primary-500">
                                </td>
                                <td class="py-4 px-4">
                                    <input type="hidden" name="items[{{ $loop->index }}][existing_picture]" value="{{ $item->picture }}">
                                    @if($item->picture)
                                        <img src="{{ Storage::url($item->picture) }}" class="h-10 w-10 object-cover rounded-md border border-gray-200 dark:border-gray-700 mb-1.5">
                                    @endif
                                    <input type="file" name="items[{{ $loop->index }}][picture]" accept="image/*"
                                           class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:bg-gray-100 dark:file:bg-gray-700 dark:text-gray-300">
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <button type="button" onclick="removeItemRow(this)"
                                            class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 inline-flex">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-gray-100 dark:border-gray-700">
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">Target Pembelian</label>
                <textarea name="target_purchase" rows="2"
                          class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2.5 resize-y">{{ old('target_purchase', $procurementRequest->target_purchase) }}</textarea>
            </div>
        </section>

        {{-- SECTION 4: TANDA TANGAN --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Persetujuan &amp; Tanda Tangan</h2>
            </header>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                @foreach([
                    ['name' => 'pic_name', 'label' => 'PIC', 'value' => $procurementRequest->pic_name],
                    ['name' => 'ops_manager_name', 'label' => 'Ops Manager', 'value' => $procurementRequest->ops_manager_name],
                    ['name' => 'fem_manager_name', 'label' => 'FEM Manager', 'value' => $procurementRequest->fem_manager_name],
                    ['name' => 'coo_name', 'label' => 'COO', 'value' => $procurementRequest->coo_name],
                ] as $sig)
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">{{ $sig['label'] }}</label>
                        <input type="text" name="{{ $sig['name'] }}" value="{{ old($sig['name'], $sig['value']) }}"
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2.5">
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ACTION BAR --}}
        <div class="sticky bottom-0 z-50 -mx-4 px-4 py-4 mt-6 bg-gradient-to-t from-slate-50 dark:from-gray-900 from-60%">
            <div class="flex items-center justify-between rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-4 shadow-lg">
                <p class="text-[13px] text-gray-500 dark:text-gray-400 hidden sm:block">
                    Pastikan seluruh data sudah benar sebelum menyimpan.
                </p>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('procurement.show', $procurementRequest) }}"
                       class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-emerald-600 hover:bg-emerald-700 px-6 py-2.5 text-sm font-medium text-white shadow-sm transition">
                        Simpan Perubahan
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
            <td class="py-4 px-4"><span class="row-number text-sm font-medium text-gray-500"></span></td>
            <td class="py-4 px-4">
                <textarea name="items[${itemIndex}][item_name]" rows="2" required
                          class="w-full rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2 resize-y"></textarea>
            </td>
            <td class="py-4 px-4">
                <textarea name="items[${itemIndex}][item_requirement_note]" rows="2"
                          class="w-full rounded-md border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm p-2 resize-y"></textarea>
            </td>
            <td class="py-4 px-4">
                <input type="number" step="0.01" name="items[${itemIndex}][quantity]" value="1" min="0.01" required
                       class="w-full border-0 border-b border-gray-200 dark:border-gray-700 dark:bg-transparent dark:text-white text-sm px-1 py-2 text-center focus:ring-0 focus:border-primary-500">
            </td>
            <td class="py-4 px-4">
                <input type="text" name="items[${itemIndex}][unit]" value="Pcs"
                       class="w-full border-0 border-b border-gray-200 dark:border-gray-700 dark:bg-transparent dark:text-white text-sm px-1 py-2 text-center focus:ring-0 focus:border-primary-500">
            </td>
            <td class="py-4 px-4">
                <input type="hidden" name="items[${itemIndex}][existing_picture]" value="">
                <input type="file" name="items[${itemIndex}][picture]" accept="image/*"
                       class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:bg-gray-100 dark:file:bg-gray-700 dark:text-gray-300">
            </td>
            <td class="py-4 px-4 text-center">
                <button type="button" onclick="removeItemRow(this)"
                        class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 inline-flex">
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
            alert('Minimal harus ada 1 item.');
            return;
        }
        btn.closest('tr').remove();
        updateRowNumbers();
    }

    document.addEventListener('DOMContentLoaded', updateRowNumbers);
</script>
@endpush
@endsection