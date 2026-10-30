{{-- Modal Revisi --}}
<div id="revisionModal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="w-full max-w-lg rounded-xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Kembalikan untuk Revisi</h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    Catatan akan dikirim ke pemohon sebagai acuan perbaikan.
                </p>
            </div>
            <button type="button" onclick="closeRevisionModal()"
                    class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="revisionForm" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="revision">

            <div class="px-6 py-5 space-y-3">
                <label for="revision_note" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Catatan Revisi <span class="text-red-500">*</span>
                </label>
                <textarea id="revision_note" name="revision_note" rows="5" required
                          maxlength="2000"
                          placeholder="Jelaskan bagian apa yang perlu diperbaiki oleh pemohon..."
                          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm resize-none"></textarea>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Wajib diisi, maksimal 2000 karakter.</span>
                    <span id="revisionCharCount" class="text-gray-400">0 / 2000</span>
                </div>
                <p id="revisionError" class="hidden text-xs text-red-600 dark:text-red-400"></p>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button type="button" onclick="closeRevisionModal()"
                        class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition">
                    Kirim Revisi
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const modal    = document.getElementById('revisionModal');
        const form     = document.getElementById('revisionForm');
        const textarea = document.getElementById('revision_note');
        const counter  = document.getElementById('revisionCharCount');
        const errorEl  = document.getElementById('revisionError');
        const submitBtn = form.querySelector('button[type="submit"]');

        window.openRevisionModal = function (actionUrl) {
            form.action = actionUrl;
            textarea.value = '';
            counter.textContent = '0 / 2000';
            errorEl.classList.add('hidden');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => textarea.focus(), 50);
        };

        window.closeRevisionModal = function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        textarea.addEventListener('input', function () {
            counter.textContent = this.value.length + ' / 2000';
            if (this.value.trim()) errorEl.classList.add('hidden');
        });

        form.addEventListener('submit', function (e) {
            if (!textarea.value.trim()) {
                e.preventDefault();
                errorEl.textContent = 'Catatan revisi wajib diisi.';
                errorEl.classList.remove('hidden');
                textarea.focus();
                return;
            }
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            submitBtn.textContent = 'Mengirim...';
        });

        // Tutup modal saat klik backdrop
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeRevisionModal();
        });

        // Esc untuk menutup
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeRevisionModal();
        });
    })();
</script>
@endpush