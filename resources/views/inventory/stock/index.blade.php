@extends('layouts.app')

@section('title', 'Inventory Stock per Departemen')
@section('page-title', 'Department Stock Items')

@section('content')
<div class="space-y-6 pb-12">

    {{-- ============================================================
         PAGE HEADER
    ============================================================ --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Department Stock Items
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if(auth()->user()->hasRole('Super Admin'))
                    Monitor physical stock availability across all departments.
                @else
                    List of stock items for department
                    <span class="font-medium text-primary-600 dark:text-primary-400">{{ auth()->user()->department->name ?? 'My Department' }}</span>.
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start">

            {{-- ✅ TOMBOL IMPORT EXCEL (BARU) --}}
            <button type="button"
                    onclick="openImportModal()"
                    class="inline-flex items-center gap-2 rounded-lg border border-blue-300 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 px-4 py-2 text-sm font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import Excel
            </button>

            {{-- ✅ TOMBOL EXPORT EXCEL --}}
            <button type="button"
                    onclick="openExportModal()"
                    class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 px-4 py-2 text-sm font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>

            @if(auth()->user()->hasRole('Super Admin'))
                <a href="{{ route('inventory.stock.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Stok
                </a>
            @endif
        </div>
    </div>

    {{-- ============================================================
         MAIN CARD (Tabel Kamu - Tidak Ada Perubahan)
    ============================================================ --}}
    <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        {{-- ... (KODE TABEL KAMU TETAP SAMA SEPERTI SEBELUMNYA) ... --}}
        {{-- Saya singkat di sini agar fokus ke fitur Import --}}
        <div class="p-6 text-center text-gray-500">
            [Tabel Data Stok Kamu Ada Di Sini]
        </div>
    </section>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     MODAL: IMPORT EXCEL DENGAN MAPPING (BARU)
═══════════════════════════════════════════════════════════════ --}}
<div id="importModal"
     class="fixed inset-0 z-50 hidden"
     role="dialog"
     aria-modal="true">

    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
         onclick="closeImportModal()"></div>

    <div class="relative z-10 flex items-center justify-center min-h-full p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 max-h-[90vh] overflow-y-auto">

            {{-- Header --}}
            <div class="flex items-start gap-3 mb-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Import Excel Stok
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Upload file Excel kamu, lalu cocokkan kolomnya dengan database.
                    </p>
                </div>
            </div>

            <form action="{{ route('inventory.stock.import-process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                
                {{-- Step 1: Upload File --}}
                <div id="step-upload">
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:bg-gray-50 dark:hover:bg-gray-700/30 transition cursor-pointer" onclick="document.getElementById('excel_file').click()">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-semibold text-primary-600">Klik untuk upload</span> atau drag and drop
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls, .csv (Max 5MB)</p>
                        <input type="file" name="excel_file" id="excel_file" class="hidden" accept=".xlsx, .xls, .csv" onchange="handleFileUpload(this)">
                    </div>
                    <div id="file-info" class="mt-3 hidden text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg flex items-center justify-between">
                        <span id="file-name"></span>
                        <button type="button" onclick="resetUpload()" class="text-red-500 hover:text-red-700 text-xs font-semibold">Hapus</button>
                    </div>
                </div>

                {{-- Step 2: Mapping Kolom (Muncul setelah file diupload) --}}
                <div id="step-mapping" class="hidden mt-6">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Pemetaan Kolom (Mapping)</h4>
                    <p class="text-xs text-gray-500 mb-4">Cocokkan kolom dari file Excel kamu dengan kolom yang ada di aplikasi.</p>
                    
                    <div class="space-y-3 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                        {{-- Baris Mapping 1 --}}
                        <div class="grid grid-cols-2 gap-3 items-center">
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Kode Item (Unique)</div>
                            <select name="mapping[item_code]" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Pilih Kolom Excel --</option>
                                <option value="A">Kolom A</option>
                                <option value="B">Kolom B</option>
                                <option value="C">Kolom C</option>
                            </select>
                        </div>
                        {{-- Baris Mapping 2 --}}
                        <div class="grid grid-cols-2 gap-3 items-center">
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama Barang</div>
                            <select name="mapping[item_name]" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Pilih Kolom Excel --</option>
                                <option value="A">Kolom A</option>
                                <option value="B">Kolom B</option>
                                <option value="C">Kolom C</option>
                            </select>
                        </div>
                        {{-- Baris Mapping 3 --}}
                        <div class="grid grid-cols-2 gap-3 items-center">
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Sisa Stok</div>
                            <select name="mapping[stock]" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Pilih Kolom Excel --</option>
                                <option value="A">Kolom A</option>
                                <option value="B">Kolom B</option>
                                <option value="C">Kolom C</option>
                            </select>
                        </div>
                        {{-- Baris Mapping 4 --}}
                        <div class="grid grid-cols-2 gap-3 items-center">
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Departemen</div>
                            <select name="mapping[department]" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Pilih Kolom Excel --</option>
                                <option value="A">Kolom A</option>
                                <option value="B">Kolom B</option>
                                <option value="C">Kolom C</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-2 pt-6 mt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" onclick="closeImportModal()"
                            class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-import" disabled
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Proses Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EXPORT EXCEL (KODE LAMA KAMU) --}}
{{-- ... (Biarkan kode modal export kamu yang lama tetap di sini) ... --}}


@push('scripts')
<script>
    // --- Script Modal Export (Kode Lama) ---
    function openExportModal() {
        document.getElementById('exportModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeExportModal() {
        document.getElementById('exportModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // --- Script Modal Import (BARU) ---
    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
        document.body.style.overflow = '';
        resetUpload(); // Reset form saat ditutup
    }

    function handleFileUpload(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('file-name').textContent = file.name;
            document.getElementById('file-info').classList.remove('hidden');
            
            // Tampilkan bagian mapping
            document.getElementById('step-mapping').classList.remove('hidden');
            
            // Aktifkan tombol submit
            document.getElementById('btn-submit-import').disabled = false;

            // Di sini kamu bisa tambahkan logic AJAX untuk membaca header Excel
            // dan mengisi dropdown <option> secara otomatis berdasarkan file yang diupload.
            // Untuk saat ini, kita isi manual "Kolom A, B, C" sebagai placeholder.
        }
    }

    function resetUpload() {
        document.getElementById('excel_file').value = '';
        document.getElementById('file-info').classList.add('hidden');
        document.getElementById('step-mapping').classList.add('hidden');
        document.getElementById('btn-submit-import').disabled = true;
        document.getElementById('file-name').textContent = '';
    }

    // Escape key untuk menutup modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeExportModal();
            closeImportModal();
        }
    });
</script>
@endpush
@endsection