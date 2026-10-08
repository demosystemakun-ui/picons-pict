{{-- resources/views/consumable-requests/_modal.blade.php --}}
<div id="createModal" class="fixed inset-0 z-[100] hidden" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeCreateModal()"></div>

    {{-- Panel wrapper --}}
    <div class="fixed inset-0 z-10 flex items-center justify-center p-0 sm:p-4">

        {{-- Panel --}}
        <div class="relative w-full h-full sm:h-auto sm:max-h-[92vh] sm:max-w-3xl
                    bg-slate-50 dark:bg-gray-900
                    sm:rounded-2xl shadow-2xl overflow-hidden
                    flex flex-col">

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 sm:px-6 py-3.5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 shrink-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white truncate">
                            Consumable Request Baru
                        </h2>
                        <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 truncate">
                            Lengkapi detail permintaan pengadaan
                        </p>
                    </div>
                </div>

                <button type="button" onclick="closeCreateModal()"
                        class="shrink-0 inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body: SCROLLABLE --}}
            <div id="createModalBody" class="flex-1 overflow-y-auto px-3 sm:px-6 py-4 sm:py-5">
                <div class="flex items-center justify-center py-20">
                    <svg class="animate-spin h-8 w-8 text-primary-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            {{-- Footer: FIXED --}}
            <div class="shrink-0 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800
                        px-4 sm:px-6 py-3 flex items-center justify-between gap-3">

                <p class="hidden sm:block text-xs text-gray-500 dark:text-gray-400">
                    Pastikan seluruh data sudah benar sebelum menyimpan.
                </p>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="button" onclick="closeCreateModal()"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>

                    {{-- Submit button di luar form → pakai form="consumableForm" --}}
                    <button type="submit" form="consumableForm"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition">
                        Simpan Request
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>