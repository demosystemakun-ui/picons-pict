@extends('layouts.app')

@section('title', 'Consumable Request')
@section('page-title', 'Consumable Request Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 pb-12 pt-6 px-4">
    @include('consumable-requests._form')
</div>
@endsection

@push('scripts')
<script>
/* Halaman standalone — submit normal (bukan AJAX) */
let itemIndex = 0;

const SPEC_LABELS = [
    'No load speed', 'Drill receptacle', 'Max Impact Rate', 'Weight',
    'Warranty', 'Voltage', 'Frequency', 'Material', 'Dimension',
    'Color', 'Power', 'Capacity', 'Size', 'Type',
];

function initConsumableForm() {
    itemIndex = 0;
    addItemRow();
}

function updateRowNumbers() {
    document.querySelectorAll('#itemsContainer .item-card').forEach((card, index) => {
        const el = card.querySelector('.row-number');
        if (el) el.textContent = index + 1;
    });
}

function addSpecRow(btn, label = '', value = '') {
    const container = btn.closest('.specs-header').nextElementSibling;
    if (!container || !container.classList.contains('specs-container')) return;

    const itemIdx = container.dataset.itemIndex;

    const optionsHtml = SPEC_LABELS.map(opt => {
        const selected = (opt === label) ? 'selected' : '';
        return `<option value="${opt}" ${selected}>${opt}</option>`;
    }).join('');

    const isCustom = label && !SPEC_LABELS.includes(label) && label !== '';

    const row = document.createElement('div');
    row.className = 'spec-row flex flex-col sm:flex-row sm:items-center gap-2';

    row.innerHTML = `
        <select onchange="handleSpecLabelChange(this)"
                class="shrink-0 w-full sm:w-44 rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm px-2 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
            <option value="" ${!label ? 'selected' : ''} disabled>-- Pilih Label --</option>
            ${optionsHtml}
            <option value="__custom__" ${isCustom ? 'selected' : ''}>+ Lainnya...</option>
        </select>

        <input type="text"
               name="items[${itemIdx}][spec_labels][]"
               value="${isCustom ? label.replace(/"/g, '&quot;') : ''}"
               placeholder="Label custom..."
               data-role="custom-label"
               class="${isCustom ? '' : 'hidden'} shrink-0 w-full sm:w-44 rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">

        <div class="flex items-center gap-2 flex-1">
            <input type="text"
                   name="items[${itemIdx}][spec_values][]"
                   value="${value.replace(/"/g, '&quot;')}"
                   placeholder="Nilai (contoh: 220V)"
                   class="flex-1 rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">

            <button type="button"
                    onclick="this.closest('.spec-row').remove(); refreshSpecDropdowns(this.closest('.item-card'))"
                    class="shrink-0 w-8 h-8 inline-flex items-center justify-center rounded-lg text-red-400 hover:text-red-600 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;
    container.appendChild(row);
    refreshSpecDropdowns(container.closest('.item-card'));
}

function handleSpecLabelChange(selectEl) {
    const row = selectEl.closest('.spec-row');
    const customInput = row.querySelector('[data-role="custom-label"]');

    if (selectEl.value === '__custom__') {
        customInput.classList.remove('hidden');
        customInput.value = '';
        customInput.focus();
    } else {
        customInput.classList.add('hidden');
        customInput.value = '';
    }

    refreshSpecDropdowns(selectEl.closest('.item-card'));
}

function refreshSpecDropdowns(itemCard) {
    if (!itemCard) return;

    const allRows = itemCard.querySelectorAll('.spec-row');

    const usedLabels = new Set();
    allRows.forEach(row => {
        const select = row.querySelector('select');
        if (select && select.value && select.value !== '__custom__' && select.value !== '') {
            usedLabels.add(select.value);
        }
    });

    allRows.forEach(row => {
        const select = row.querySelector('select');
        if (!select) return;

        const currentValue = select.value;

        Array.from(select.options).forEach(opt => {
            if (opt.value === '' || opt.value === '__custom__') return;

            const shouldHide = usedLabels.has(opt.value) && opt.value !== currentValue;
            opt.hidden = shouldHide;
            opt.disabled = shouldHide;
        });
    });
}

function addItemRow() {
    const container = document.getElementById('itemsContainer');
    if (!container) return;

    const div = document.createElement('div');
    div.className = 'item-card bg-gray-50/50 dark:bg-gray-900/30 rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-4 relative';

    div.innerHTML = `
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <div class="flex items-center gap-2">
                <span class="row-number inline-flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 text-xs font-bold"></span>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Item</span>
            </div>
            <button type="button" onclick="removeItemRow(this)"
                    class="text-red-400 hover:text-red-600 dark:hover:text-red-300 transition-colors p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 inline-flex">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
            <div class="md:col-span-2">
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                    Item Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="items[${itemIndex}][item_name]" required
                       placeholder="Contoh: Electric Impact Drill"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Brand</label>
                <input type="text" name="items[${itemIndex}][brand]" placeholder="Contoh: Doliz"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Type</label>
                <input type="text" name="items[${itemIndex}][type]" placeholder="Contoh: BA670"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Model</label>
                <input type="text" name="items[${itemIndex}][model]" placeholder="Contoh: 13mm"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Capacity</label>
                <input type="text" name="items[${itemIndex}][capacity]" placeholder="Contoh: 550w"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>

        <div class="mb-4">
            <div class="specs-header flex items-center justify-between mb-1.5">
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                    Specs / Material Details <span class="text-gray-400 font-normal normal-case">(opsional)</span>
                </label>
                <button type="button" onclick="addSpecRow(this)"
                        class="inline-flex items-center gap-1 text-[11px] font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Baris
                </button>
            </div>
            <div class="specs-container space-y-2" data-item-index="${itemIndex}"></div>
            <p class="text-[10px] text-gray-400 mt-1.5">Pilih label, lalu tulis nilainya.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                    Quantity <span class="text-red-500">*</span>
                </label>
                <input type="number" name="items[${itemIndex}][quantity]" value="1" min="1" step="any" required
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                    Unit <span class="text-red-500">*</span>
                </label>
                <input type="text" name="items[${itemIndex}][unit]" value="Pcs" required
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Picture</label>
                <div class="flex justify-center px-3 py-2 border-2 border-gray-300 dark:border-gray-700 border-dashed rounded-lg hover:border-primary-400 dark:hover:border-primary-500 transition-colors group cursor-pointer bg-white dark:bg-gray-900">
                    <div class="space-y-0.5 text-center">
                        <svg class="mx-auto h-5 w-5 text-gray-400 group-hover:text-primary-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="text-[10px] text-gray-600 dark:text-gray-400">
                            <label class="relative cursor-pointer font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                <span>Upload</span>
                                <input type="file" name="items[${itemIndex}][picture]" accept="image/*" class="sr-only">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Purpose</p>
            <div>
                <label class="block text-[11px] italic text-gray-500 mb-1">1. Why you want to purchase it?</label>
                <textarea name="items[${itemIndex}][purpose_1]" rows="2" required
                          class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-xs p-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-none"></textarea>
            </div>
            <div>
                <label class="block text-[11px] italic text-gray-500 mb-1">2. Which process/activity will it support?</label>
                <textarea name="items[${itemIndex}][purpose_2]" rows="2" required
                          class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-xs p-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-none"></textarea>
            </div>
            <div>
                <label class="block text-[11px] italic text-gray-500 mb-1">3. What operational/financial efficiency?</label>
                <textarea name="items[${itemIndex}][purpose_3]" rows="2" required
                          class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-xs p-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-none"></textarea>
            </div>
        </div>
    `;
    container.appendChild(div);

    const addBtn = div.querySelector('.specs-header button');
    addSpecRow(addBtn);
    refreshSpecDropdowns(div);

    itemIndex++;
    updateRowNumbers();
}

function removeItemRow(btn) {
    btn.closest('.item-card').remove();
    updateRowNumbers();
}

document.addEventListener('DOMContentLoaded', function () {
    initConsumableForm();
});
</script>
@endpush