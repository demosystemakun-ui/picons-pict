{{-- ═══════════════════════════════════════════════════════════════
     TOAST HASIL IMPORT / ERROR (fixed, pojok kanan atas)
     Hapus bagian 'success' kalau layout kamu sudah menampilkannya.
═══════════════════════════════════════════════════════════════ --}}
@if ($errors->any() || session('warning') || session('error') || session('success') || session('import_report'))
    <div id="importToast" class="fixed top-4 right-4 z-[60] w-full max-w-md rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xl p-4 text-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1.5 min-w-0">
                @if (session('success'))
                    <p class="font-semibold text-emerald-700 dark:text-emerald-400">{{ session('success') }}</p>
                @endif
                @if (session('warning'))
                    <p class="font-semibold text-amber-700 dark:text-amber-400">{{ session('warning') }}</p>
                @endif
                @if (session('error'))
                    <p class="font-semibold text-red-700 dark:text-red-400">{{ session('error') }}</p>
                @endif
                @if ($errors->any())
                    <ul class="list-disc pl-5 text-red-700 dark:text-red-400">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
                @if (session('import_report'))
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 pt-1">Baris yang dilewati:</p>
                    <ul class="max-h-48 overflow-y-auto list-disc pl-5 text-xs text-gray-600 dark:text-gray-400">
                        @foreach (session('import_report') as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <button type="button" onclick="document.getElementById('importToast').remove()"
                    class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
        </div>
    </div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     MODAL: IMPORT EXCEL DENGAN AUTO-DETECT KOLOM
═══════════════════════════════════════════════════════════════ --}}
<div id="importModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeImportModal()"></div>

    <div class="relative z-10 flex items-center justify-center min-h-full p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 max-h-[90vh] overflow-y-auto">

            <div class="flex items-start gap-3 mb-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Import Excel Stok</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Upload file apa saja. Sistem akan mendeteksi kolomnya otomatis, kamu tinggal cek.
                    </p>
                </div>
            </div>

            <form action="{{ route('inventory.stock.import-process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf

                {{-- Upload --}}
                <div id="step-upload">
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:bg-gray-50 dark:hover:bg-gray-700/30 transition cursor-pointer"
                         onclick="document.getElementById('excel_file').click()">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-semibold text-blue-600">Klik untuk upload</span> file Excel
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls, .csv (Max 5MB)</p>
                        <input type="file" name="excel_file" id="excel_file" class="hidden"
                               accept=".xlsx,.xls,.csv" onchange="analyzeImportFile(true)">
                    </div>

                    <div id="file-info" class="mt-3 hidden text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg flex items-center justify-between">
                        <span id="file-name"></span>
                        <button type="button" onclick="resetUpload()" class="text-red-500 hover:text-red-700 text-xs font-semibold">Hapus</button>
                    </div>

                    <p id="import-loading" class="hidden mt-3 text-sm text-blue-600">Membaca file &amp; mendeteksi kolom…</p>
                    <p id="import-error" class="hidden mt-3 text-sm text-red-600"></p>
                </div>

                {{-- Mapping --}}
                <div id="step-mapping" class="hidden mt-6">
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div id="sheet-wrap">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Sheet</label>
                            <select name="sheet" id="import-sheet" onchange="analyzeImportFile(false, true)"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm"></select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Baris header (nomor baris di Excel)</label>
                            <input type="number" min="1" name="header_row" id="import-header-row"
                                   onchange="analyzeImportFile(false)"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                        </div>
                    </div>

                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Pemetaan Kolom</h4>
                    <p class="text-xs text-gray-500 mb-3">Hasil deteksi otomatis sudah terpilih. Ubah jika ada yang kurang tepat.</p>

                    <div id="mapping-rows" class="space-y-3 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700"></div>

                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Kategori default (untuk item baru)</label>
                            <select name="default_category_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                                @foreach (($categories ?? []) as $c)
                                    <option value="{{ $c->id }}">{{ $c->name ?? $c->category_name ?? ('#' . $c->id) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Satuan default (untuk item baru)</label>
                            <select name="default_unit_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                                @foreach (($units ?? []) as $u)
                                    <option value="{{ $u->id }}">{{ $u->name ?? $u->code ?? ('#' . $u->id) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if (empty($categories) || count($categories) === 0)
                        <p class="mt-2 text-xs text-amber-600">Belum ada kategori. Buat dulu di menu Categories agar item baru bisa diimport.</p>
                    @endif

                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mt-5 mb-2">
                        Preview <span id="import-total" class="text-xs font-normal text-gray-500"></span>
                    </h4>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-xs text-left" id="import-preview"></table>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-6 mt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" onclick="closeImportModal()"
                            class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-import" disabled
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Proses Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const IMPORT_ANALYZE_URL = @json(route('inventory.stock.import-analyze'));
    const IMPORT_FIELDS = [
        { key: 'item_code',  label: 'Kode Item',   required: false },
        { key: 'item_name',  label: 'Nama Barang', required: true  },
        { key: 'stock',      label: 'Sisa Stok',   required: true  },
        { key: 'department', label: 'Departemen',  required: true  },
    ];

    const $imp = (id) => document.getElementById(id);
    const escHtml = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function openImportModal() {
        $imp('importModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeImportModal() {
        $imp('importModal').classList.add('hidden');
        document.body.style.overflow = '';
        resetUpload();
    }

    function resetUpload() {
        $imp('excel_file').value = '';
        $imp('file-info').classList.add('hidden');
        $imp('step-mapping').classList.add('hidden');
        $imp('import-error').classList.add('hidden');
        $imp('import-loading').classList.add('hidden');
        $imp('btn-submit-import').disabled = true;
        $imp('file-name').textContent = '';
    }

    /**
     * @param {boolean} fresh      file baru dipilih (header & sheet auto)
     * @param {boolean} sheetOnly  user ganti sheet (header auto lagi)
     */
    async function analyzeImportFile(fresh = false, sheetOnly = false) {
        const file = $imp('excel_file').files[0];
        if (!file) return;

        $imp('file-name').textContent = file.name;
        $imp('file-info').classList.remove('hidden');
        $imp('import-error').classList.add('hidden');
        $imp('import-loading').classList.remove('hidden');

        const fd = new FormData();
        fd.append('excel_file', file);
        fd.append('_token', document.querySelector('#importForm input[name="_token"]').value);
        if (!fresh) fd.append('sheet', $imp('import-sheet').value || 0);
        if (!fresh && !sheetOnly && $imp('import-header-row').value) {
            fd.append('header_row', $imp('import-header-row').value);
        }

        try {
            const res  = await fetch(IMPORT_ANALYZE_URL, {
                method: 'POST', body: fd,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json().catch(() => ({}));

            $imp('import-loading').classList.add('hidden');

            if (!res.ok) {
                const firstErr = data.errors ? Object.values(data.errors)[0][0] : null;
                showImportError(data.message && !data.errors ? data.message : (firstErr || 'Gagal membaca file.'));
                return;
            }
            renderImportMapping(data);
        } catch (e) {
            $imp('import-loading').classList.add('hidden');
            showImportError('Terjadi kesalahan jaringan/server: ' + e.message);
        }
    }

    function showImportError(msg) {
        const el = $imp('import-error');
        el.textContent = msg;
        el.classList.remove('hidden');
        $imp('step-mapping').classList.add('hidden');
        $imp('btn-submit-import').disabled = true;
    }

    function renderImportMapping(data) {
        // Sheet & header
        $imp('import-sheet').innerHTML = data.sheets
            .map((n, i) => `<option value="${i}" ${i === data.sheet ? 'selected' : ''}>${escHtml(n)}</option>`).join('');
        $imp('sheet-wrap').classList.toggle('hidden', data.sheets.length < 2);
        $imp('import-header-row').value = data.header_row;

        // Baris mapping
        const colOptions = data.columns
            .map(c => `<option value="${c.index}">${escHtml(c.letter)} — ${escHtml(c.label)}</option>`).join('');

        $imp('mapping-rows').innerHTML = IMPORT_FIELDS.map(f => {
            const s = data.suggested[f.key];
            let badge;
            if (s && s.score >= 90)      badge = '<span class="text-xs text-emerald-600">✓ terdeteksi otomatis</span>';
            else if (s)                  badge = '<span class="text-xs text-amber-600">⚠ perkiraan, mohon dicek</span>';
            else if (f.required)         badge = '<span class="text-xs text-red-600">tidak terdeteksi, pilih manual</span>';
            else                         badge = '<span class="text-xs text-gray-400">opsional</span>';

            return `
              <div class="grid grid-cols-2 gap-3 items-center">
                <div>
                  <div class="text-sm font-medium text-gray-700 dark:text-gray-300">${f.label}${f.required ? ' <span class="text-red-500">*</span>' : ''}</div>
                  ${badge}
                </div>
                <select name="mapping[${f.key}]" data-field="${f.key}" onchange="checkImportReady()"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                  <option value="">${f.required ? '-- Pilih kolom --' : '-- Tidak dipakai --'}</option>
                  ${colOptions}
                </select>
              </div>`;
        }).join('');

        IMPORT_FIELDS.forEach(f => {
            const s = data.suggested[f.key];
            if (s) document.querySelector(`#mapping-rows select[data-field="${f.key}"]`).value = s.column;
        });

        // Preview
        $imp('import-total').textContent = `(${data.total_rows} baris data)`;
        $imp('import-preview').innerHTML =
            '<thead class="bg-gray-50 dark:bg-gray-900/40"><tr>' +
            data.preview_heads.map(h => `<th class="px-3 py-2 font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">${escHtml(h)}</th>`).join('') +
            '</tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-700">' +
            data.preview.map(r => '<tr>' + r.map(c => `<td class="px-3 py-1.5 text-gray-700 dark:text-gray-300 whitespace-nowrap">${escHtml(c)}</td>`).join('') + '</tr>').join('') +
            '</tbody>';

        $imp('step-mapping').classList.remove('hidden');
        checkImportReady();
    }

    function checkImportReady() {
        const ok = IMPORT_FIELDS.filter(f => f.required).every(f => {
            const sel = document.querySelector(`#mapping-rows select[data-field="${f.key}"]`);
            return sel && sel.value !== '';
        });
        $imp('btn-submit-import').disabled = !ok;
    }

    $imp('importForm').addEventListener('submit', () => {
        const b = $imp('btn-submit-import');
        b.disabled = true;
        b.textContent = 'Memproses…';
    });
</script>
@endpush