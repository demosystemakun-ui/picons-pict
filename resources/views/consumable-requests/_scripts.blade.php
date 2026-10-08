{{-- resources/views/consumable-requests/_scripts.blade.php --}}
<script>
/* ═══════════════════════════════════════════════════════
   MODAL CREATE CONSUMABLE REQUEST
═══════════════════════════════════════════════════════ */
let createFormLoaded = false;

function openCreateModal() {
    const modal = document.getElementById('createModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    if (!createFormLoaded) loadCreateForm();
}

function closeCreateModal() {
    const modal = document.getElementById('createModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

async function loadCreateForm() {
    const body = document.getElementById('createModalBody');

    try {
        const res = await fetch('/consumable-requests/create-form', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'ngrok-skip-browser-warning': 'true',
            }
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);

        const html = await res.text();
        body.innerHTML = html;
        createFormLoaded = true;

        initConsumableForm();
        bindCreateFormSubmit();
    } catch (err) {
        console.error('LOAD ERROR:', err);
        body.innerHTML = `
            <div class="py-16 text-center px-4">
                <p class="text-red-500 text-sm font-semibold">Gagal memuat form</p>
                <p class="text-xs text-gray-500 mt-2 break-all">${err.message}</p>
                <button onclick="loadCreateForm()" class="mt-3 text-primary-600 underline text-sm">Muat ulang</button>
            </div>
        `;
    }
}

function bindCreateFormSubmit() {
    const form = document.getElementById('consumableForm');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const submitBtn = document.querySelector('button[form="consumableForm"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Menyimpan...';

        form.querySelectorAll('.field-error').forEach(el => el.remove());
        form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

        try {
            const formData = new FormData(form);

            const res = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                    || form.querySelector('input[name="_token"]').value,
                }
            });

            if (res.status === 422) {
                const data = await res.json();
                showFormErrors(data.errors, form);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                return;
            }

            if (!res.ok) throw new Error('Server error');

            const data = await res.json();

            if (data.success) {
                closeCreateModal();
                window.location.reload();
            } else {
                throw new Error(data.message || 'Gagal menyimpan');
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan: ' + err.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

function showFormErrors(errors, form) {
    let firstErrorField = null;

    Object.keys(errors).forEach(key => {
        let inputName = key;
        const match = key.match(/^([^\d]+)\.(\d+)\.(.+)$/);
        if (match) inputName = `${match[1]}[${match[2]}][${match[3]}]`;

        const input = form.querySelector(`[name="${inputName}"]`);
        if (input) {
            input.classList.add('border-red-500');
            if (!firstErrorField) firstErrorField = input;

            const err = document.createElement('p');
            err.className = 'field-error text-xs text-red-500 mt-1';
            err.textContent = errors[key][0];
            input.parentNode.appendChild(err);
        }
    });

    if (firstErrorField) {
        firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstErrorField.focus();
    }
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('createModal');
        if (modal && !modal.classList.contains('hidden')) closeCreateModal();
    }
});

/* ═══════════════════════════════════════════════════════
   FORM CONSUMABLE — INIT
═══════════════════════════════════════════════════════ */
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

/* ═══════════════════════════════════════════════════════
   SPECS ROW — SEARCHABLE COMBOBOX
═══════════════════════════════════════════════════════ */
function addSpecRow(btn, label = '', value = '') {
    const container = btn.closest('.specs-header').nextElementSibling;
    if (!container || !container.classList.contains('specs-container')) return;

    const itemIdx = container.dataset.itemIndex;
    const rowIdx  = container.children.length;

    const isCustom = label && !SPEC_LABELS.includes(label) && label !== '';

    const row = document.createElement('div');
    row.className = 'spec-row flex flex-col sm:flex-row sm:items-start gap-2';

    row.innerHTML = `
        {{-- Spec Label: Searchable Combobox --}}
        <div class="spec-label-field relative shrink-0 w-full sm:w-48"
             data-item-idx="${itemIdx}"
             data-row-idx="${rowIdx}">

            <input type="hidden"
                   name="items[${itemIdx}][spec_labels][]"
                   class="spec-label-value"
                   value="${isCustom ? escapeAttr(label) : ''}">

            <button type="button"
                    onclick="toggleSpecLabelDropdown(this, event)"
                    class="spec-label-trigger w-full flex items-center justify-between gap-2
                           rounded-lg border border-gray-300 dark:border-gray-700
                           dark:bg-gray-900 dark:text-white text-sm
                           px-2.5 py-2 text-left
                           focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                <span class="spec-label-trigger-text truncate ${
                    isCustom ? 'text-primary-600 dark:text-primary-400 font-medium'
                             : (label ? '' : 'text-gray-400 dark:text-gray-500')
                }">
                    ${label ? escapeHtml(label) : '-- Pilih Label --'}
                </span>
                <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="spec-label-dropdown hidden absolute z-30 mt-1 w-full sm:w-56
                        bg-white dark:bg-gray-800
                        border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg
                        overflow-hidden">

                <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                    <input type="text"
                           class="spec-label-search w-full rounded-md
                                  border border-gray-200 dark:border-gray-600
                                  dark:bg-gray-900 dark:text-white text-sm
                                  px-2.5 py-1.5
                                  focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Cari label..."
                           oninput="filterSpecLabelOptions(this)"
                           onkeydown="handleSpecLabelKeydown(this, event)">
                </div>

                <div class="spec-label-options max-h-48 overflow-y-auto py-1"></div>
            </div>
        </div>

        {{-- Value Input --}}
        <div class="flex items-center gap-2 flex-1 w-full">
            <input type="text"
                   name="items[${itemIdx}][spec_values][]"
                   value="${value ? escapeAttr(value) : ''}"
                   placeholder="Nilai (contoh: 220V)"
                   class="flex-1 rounded-lg border border-gray-300 dark:border-gray-700
                          dark:bg-gray-900 dark:text-white text-sm px-3 py-2
                          focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">

            <button type="button"
                    onclick="this.closest('.spec-row').remove(); refreshSpecDropdowns(this.closest('.item-card'))"
                    class="shrink-0 w-9 h-9 inline-flex items-center justify-center rounded-lg
                           text-red-400 hover:text-red-600 dark:hover:text-red-300
                           hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;

    container.appendChild(row);

    const field = row.querySelector('.spec-label-field');
    renderSpecLabelOptions(field, '');
    refreshSpecDropdowns(container.closest('.item-card'));
}

/* ═══════════════════════════════════════════════════════
   SPEC LABEL — COMBOBOX LOGIC
═══════════════════════════════════════════════════════ */
function toggleSpecLabelDropdown(triggerBtn, ev) {
    if (ev) ev.stopPropagation();

    const field = triggerBtn.closest('.spec-label-field');
    if (!field) return;

    const dropdown = field.querySelector('.spec-label-dropdown');
    const searchInput = field.querySelector('.spec-label-search');

    document.querySelectorAll('.spec-label-dropdown').forEach(d => {
        if (d !== dropdown) d.classList.add('hidden');
    });

    const isOpen = !dropdown.classList.contains('hidden');

    if (isOpen) {
        dropdown.classList.add('hidden');
    } else {
        dropdown.classList.remove('hidden');
        searchInput.value = '';
        renderSpecLabelOptions(field, '');
        setTimeout(() => searchInput.focus(), 50);
    }
}

function renderSpecLabelOptions(field, query = '') {
    if (!field) return;

    const optionsEl = field.querySelector('.spec-label-options');
    const itemIdx   = field.dataset.itemIdx;
    const rowIdx    = parseInt(field.dataset.rowIdx || '0', 10);
    const current   = field.querySelector('.spec-label-value')?.value || '';

    const itemCard = field.closest('.item-card');
    const usedLabels = new Set();
    if (itemCard) {
        itemCard.querySelectorAll('.spec-label-field').forEach(f => {
            const v = f.querySelector('.spec-label-value')?.value;
            if (v && f !== field) usedLabels.add(v);
        });
    }

    const q = (query || '').toLowerCase().trim();

    const filtered = SPEC_LABELS.filter(opt => {
        if (usedLabels.has(opt)) return false;
        if (!q) return true;
        return opt.toLowerCase().includes(q);
    });

    let html = '';

    if (filtered.length > 0) {
        html += filtered.map(opt => {
            const isSelected = (opt === current);
            return `
                <button type="button"
                        onclick="selectSpecLabel(${itemIdx}, ${rowIdx}, '${escapeAttr(opt)}')"
                        class="w-full flex items-center justify-between gap-2 text-left px-3 py-2 text-sm
                               ${isSelected
                                    ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-semibold'
                                    : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'}
                               transition">
                    <span class="truncate">${escapeHtml(opt)}</span>
                    ${isSelected ? `
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    ` : ''}
                </button>
            `;
        }).join('');
    } else {
        html += `<div class="px-3 py-2 text-xs text-gray-400 text-center">Tidak ada label</div>`;
    }

    if (q && !SPEC_LABELS.some(o => o.toLowerCase() === q)) {
        html += `
            <div class="border-t border-gray-100 dark:border-gray-700 mt-1 pt-1">
                <button type="button"
                        onclick="selectSpecLabel(${itemIdx}, ${rowIdx}, '${escapeAttr(query)}')"
                        class="w-full text-left px-3 py-2 text-sm
                               text-primary-600 dark:text-primary-400
                               hover:bg-primary-50 dark:hover:bg-primary-900/30 transition">
                    + Pakai "<span class="font-semibold">${escapeHtml(query)}</span>"
                </button>
            </div>
        `;
    }

    optionsEl.innerHTML = html;
}

function filterSpecLabelOptions(searchInput) {
    const field = searchInput.closest('.spec-label-field');
    renderSpecLabelOptions(field, searchInput.value);
}

function handleSpecLabelKeydown(searchInput, event) {
    if (event.key === 'Escape') {
        searchInput.closest('.spec-label-field').querySelector('.spec-label-dropdown').classList.add('hidden');
        event.preventDefault();
    } else if (event.key === 'Enter') {
        event.preventDefault();
        const firstBtn = searchInput.closest('.spec-label-field').querySelector('.spec-label-options button');
        if (firstBtn) firstBtn.click();
    }
}

function selectSpecLabel(itemIdx, rowIdx, value) {
    const field = document.querySelector(
        `.spec-label-field[data-item-idx="${itemIdx}"][data-row-idx="${rowIdx}"]`
    );
    if (!field) return;

    const hiddenInput = field.querySelector('.spec-label-value');
    const triggerText = field.querySelector('.spec-label-trigger-text');

    hiddenInput.value = value;
    triggerText.textContent = value;
    triggerText.classList.remove('text-gray-400', 'dark:text-gray-500');

    const isCustom = !SPEC_LABELS.includes(value);
    if (isCustom) {
        triggerText.classList.add('text-primary-600', 'dark:text-primary-400', 'font-medium');
    } else {
        triggerText.classList.remove('text-primary-600', 'dark:text-primary-400', 'font-medium');
    }

    field.querySelector('.spec-label-dropdown').classList.add('hidden');

    const itemCard = field.closest('.item-card');
    if (itemCard) {
        itemCard.querySelectorAll('.spec-label-field').forEach(f => {
            if (f !== field) renderSpecLabelOptions(f, '');
        });
    }
}

function refreshSpecDropdowns(itemCard) {
    if (!itemCard) return;
    itemCard.querySelectorAll('.spec-label-field').forEach(field => {
        renderSpecLabelOptions(field, '');
    });
}

/* ═══════════════════════════════════════════════════════
   ITEM ROW
═══════════════════════════════════════════════════════ */
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
                    Specs / Material Details
                    <span class="text-gray-400 font-normal normal-case">(opsional)</span>
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
            <p class="text-[10px] text-gray-400 mt-1.5">Pilih label, lalu tulis nilainya. Label yang sudah dipakai akan otomatis hilang dari pilihan.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                    Quantity <span class="text-red-500">*</span>
                </label>
                <input type="number" name="items[${itemIndex}][quantity]" value="1" min="1" step="any" required
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
            </div>

            {{-- ✅ UNIT: Searchable Combobox --}}
            <div class="unit-field relative" data-unit-idx="${itemIndex}">
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                    Unit <span class="text-red-500">*</span>
                </label>

                <input type="hidden" name="items[${itemIndex}][unit]" class="unit-value" value="Pcs" required>

                <button type="button"
                        onclick="toggleUnitDropdown(${itemIndex}, event)"
                        class="unit-trigger w-full flex items-center justify-between gap-2 rounded-lg
                               border border-gray-300 dark:border-gray-700
                               dark:bg-gray-900 dark:text-white text-sm
                               px-3 py-2 text-left
                               focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                    <span class="unit-trigger-text truncate">Pcs</span>
                    <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div class="unit-dropdown hidden absolute z-30 mt-1 w-full
                            bg-white dark:bg-gray-800
                            border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg
                            overflow-hidden">

                    <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                        <input type="text"
                               class="unit-search w-full rounded-md border border-gray-200 dark:border-gray-600
                                      dark:bg-gray-900 dark:text-white text-sm px-2.5 py-1.5
                                      focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="Cari unit..."
                               oninput="filterUnitOptions(${itemIndex}, this.value)"
                               onkeydown="handleUnitKeydown(${itemIndex}, event)">
                    </div>

                    <div class="unit-options max-h-48 overflow-y-auto py-1"></div>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Picture</label>

                <div class="picture-field" data-picture-idx="${itemIndex}">
                    <input type="file"
                           name="items[${itemIndex}][picture]"
                           id="picture-input-${itemIndex}"
                           accept="image/*"
                           class="sr-only picture-input"
                           onchange="handlePictureChange(this)">

                    <label for="picture-input-${itemIndex}"
                           class="picture-upload-label flex flex-col justify-center items-center px-3 py-3
                                  border-2 border-gray-300 dark:border-gray-700 border-dashed rounded-lg
                                  hover:border-primary-400 dark:hover:border-primary-500
                                  transition-colors group cursor-pointer bg-white dark:bg-gray-900 min-h-[80px]">
                        <svg class="mx-auto h-6 w-6 text-gray-400 group-hover:text-primary-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="mt-1 text-[11px] font-medium text-primary-600 dark:text-primary-400">Upload Gambar</span>
                    </label>

                    <div class="picture-preview hidden relative rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
                        <img src="" alt="Preview" class="picture-preview-img w-full h-32 object-contain bg-gray-50 dark:bg-gray-800">

                        <button type="button"
                                onclick="clearPicture(${itemIndex})"
                                class="absolute top-1.5 right-1.5 inline-flex h-7 w-7 items-center justify-center
                                       rounded-full bg-red-500 hover:bg-red-600 text-white shadow-md transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <div class="px-2 py-1.5 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
                            <p class="picture-preview-name text-[10px] text-gray-600 dark:text-gray-400 truncate"></p>
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

    // ✅ Init unit combobox
    renderUnitOptions(itemIndex, '');
    setUnitValue(itemIndex, 'Pcs');

    itemIndex++;
    updateRowNumbers();
}

function removeItemRow(btn) {
    btn.closest('.item-card').remove();
    updateRowNumbers();
}

/* ═══════════════════════════════════════════════════════
   UNIT — SEARCHABLE COMBOBOX
═══════════════════════════════════════════════════════ */
function getUnitOptions() {
    const raw = window.__UNIT_OPTIONS || [];
    return raw.map(u => {
        if (typeof u === 'string') return { name: u, code: u };
        return { name: u.name || u.code || '', code: u.code || u.name || '' };
    }).filter(u => u.name);
}

function toggleUnitDropdown(itemIdx, ev) {
    if (ev) ev.stopPropagation();

    const wrapper = document.querySelector(`.unit-field[data-unit-idx="${itemIdx}"]`);
    if (!wrapper) return;

    const dropdown = wrapper.querySelector('.unit-dropdown');
    const searchInput = wrapper.querySelector('.unit-search');

    document.querySelectorAll('.unit-dropdown').forEach(d => {
        if (d !== dropdown) d.classList.add('hidden');
    });

    const isOpen = !dropdown.classList.contains('hidden');

    if (isOpen) {
        dropdown.classList.add('hidden');
    } else {
        dropdown.classList.remove('hidden');
        searchInput.value = '';
        renderUnitOptions(itemIdx, '');
        setTimeout(() => searchInput.focus(), 50);
    }
}

function renderUnitOptions(itemIdx, query = '') {
    const wrapper = document.querySelector(`.unit-field[data-unit-idx="${itemIdx}"]`);
    if (!wrapper) return;

    const optionsEl = wrapper.querySelector('.unit-options');
    const current   = wrapper.querySelector('.unit-value')?.value || '';

    const allOpts = getUnitOptions();
    const q = (query || '').toLowerCase().trim();

    const filtered = q
        ? allOpts.filter(u =>
            u.name.toLowerCase().includes(q) ||
            u.code.toLowerCase().includes(q))
        : allOpts;

    if (filtered.length === 0) {
        if (q) {
            optionsEl.innerHTML = `
                <button type="button"
                        onclick="selectUnitOption(${itemIdx}, '${escapeAttr(query)}')"
                        class="w-full text-left px-3 py-2 text-sm
                               text-primary-600 dark:text-primary-400
                               hover:bg-primary-50 dark:hover:bg-primary-900/30 transition">
                    + Pakai "<span class="font-semibold">${escapeHtml(query)}</span>"
                </button>
            `;
        } else {
            optionsEl.innerHTML = `
                <div class="px-3 py-2 text-xs text-gray-400 text-center">Tidak ada unit</div>
            `;
        }
        return;
    }

    optionsEl.innerHTML = filtered.map(u => {
        const isSelected = (u.name === current || u.code === current);
        return `
            <button type="button"
                    onclick="selectUnitOption(${itemIdx}, '${escapeAttr(u.name)}')"
                    class="w-full flex items-center justify-between gap-2 text-left px-3 py-2 text-sm
                           ${isSelected
                                ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-semibold'
                                : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'}
                           transition">
                <span class="truncate">${escapeHtml(u.name)}</span>
                ${isSelected ? `
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                ` : ''}
            </button>
        `;
    }).join('');
}

function filterUnitOptions(itemIdx, value) {
    renderUnitOptions(itemIdx, value);
}

function handleUnitKeydown(itemIdx, event) {
    if (event.key === 'Escape') {
        const wrapper = document.querySelector(`.unit-field[data-unit-idx="${itemIdx}"]`);
        if (wrapper) wrapper.querySelector('.unit-dropdown').classList.add('hidden');
        event.preventDefault();
    } else if (event.key === 'Enter') {
        event.preventDefault();
        const wrapper = document.querySelector(`.unit-field[data-unit-idx="${itemIdx}"]`);
        if (!wrapper) return;
        const firstBtn = wrapper.querySelector('.unit-options button');
        if (firstBtn) firstBtn.click();
    }
}

function selectUnitOption(itemIdx, value) {
    const wrapper = document.querySelector(`.unit-field[data-unit-idx="${itemIdx}"]`);
    if (!wrapper) return;

    wrapper.querySelector('.unit-value').value = value;
    wrapper.querySelector('.unit-trigger-text').textContent = value;
    wrapper.querySelector('.unit-dropdown').classList.add('hidden');
}

function setUnitValue(itemIdx, value) {
    const wrapper = document.querySelector(`.unit-field[data-unit-idx="${itemIdx}"]`);
    if (!wrapper) return;
    wrapper.querySelector('.unit-value').value = value;
    wrapper.querySelector('.unit-trigger-text').textContent = value;
}

/* ═══════════════════════════════════════════════════════
   PICTURE UPLOAD — PREVIEW & CLEAR
═══════════════════════════════════════════════════════ */
function handlePictureChange(input) {
    const file = input.files?.[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        alert('File harus berupa gambar (JPG, PNG, WebP, dll).');
        input.value = '';
        return;
    }

    const MAX_SIZE = 2 * 1024 * 1024;
    if (file.size > MAX_SIZE) {
        alert('Ukuran gambar maksimal 2MB.');
        input.value = '';
        return;
    }

    const wrapper = input.closest('.picture-field');
    if (!wrapper) return;

    const label   = wrapper.querySelector('.picture-upload-label');
    const preview = wrapper.querySelector('.picture-preview');
    const img     = wrapper.querySelector('.picture-preview-img');
    const nameEl  = wrapper.querySelector('.picture-preview-name');

    const reader = new FileReader();
    reader.onload = function (e) {
        img.src = e.target.result;
        nameEl.textContent = file.name;

        label.classList.add('hidden');
        preview.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

function clearPicture(itemIdx) {
    const wrapper = document.querySelector(`.picture-field[data-picture-idx="${itemIdx}"]`);
    if (!wrapper) return;

    const input   = wrapper.querySelector('.picture-input');
    const label   = wrapper.querySelector('.picture-upload-label');
    const preview = wrapper.querySelector('.picture-preview');
    const img     = wrapper.querySelector('.picture-preview-img');
    const nameEl  = wrapper.querySelector('.picture-preview-name');

    input.value = '';
    img.src = '';
    nameEl.textContent = '';

    preview.classList.add('hidden');
    label.classList.remove('hidden');
}

/* ═══════════════════════════════════════════════════════
   GLOBAL: Close dropdowns on outside click
═══════════════════════════════════════════════════════ */
document.addEventListener('click', function (e) {
    // Close unit dropdowns
    if (!e.target.closest('.unit-field')) {
        document.querySelectorAll('.unit-dropdown').forEach(d => d.classList.add('hidden'));
    }
    // Close spec label dropdowns
    if (!e.target.closest('.spec-label-field')) {
        document.querySelectorAll('.spec-label-dropdown').forEach(d => d.classList.add('hidden'));
    }
});

/* ═══════════════════════════════════════════════════════
   UTILS
═══════════════════════════════════════════════════════ */
function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escapeAttr(str) {
    return String(str ?? '')
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/"/g, '&quot;');
}
</script>