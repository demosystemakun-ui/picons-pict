
<div x-data="{
        open: false,
        message: '',
        form: null,
        trigger(el, msg) {
            this.form = el;
            this.message = msg;
            this.open = true;
        },
        confirm() {
            if (this.form) this.form.submit();
        }
     }"
     x-on:confirm-open.window="trigger($event.detail.form, $event.detail.message)"
     x-show="open"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[110] flex items-center justify-center p-4">

    
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"
         @click="open = false"></div>

    
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="relative w-full max-w-sm bg-white dark:bg-gray-800 rounded-2xl shadow-2xl
                border border-gray-100 dark:border-gray-700 overflow-hidden">

        <div class="p-6">
            <div class="flex flex-col items-center text-center">

                
                <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/40
                            flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                    Konfirmasi
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="message"></p>
            </div>
        </div>

        
        <div class="px-6 pb-6 flex items-center gap-3">
            <button type="button"
                    @click="open = false"
                    class="flex-1 inline-flex items-center justify-center rounded-xl
                           border border-gray-200 dark:border-gray-600
                           bg-white dark:bg-gray-800
                           text-gray-700 dark:text-gray-300 text-sm font-medium py-2.5
                           hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Batal
            </button>
            <button type="button"
                    @click="confirm()"
                    class="flex-1 inline-flex items-center justify-center rounded-xl
                           bg-red-600 hover:bg-red-700
                           text-white text-sm font-medium py-2.5
                           transition-colors shadow-sm shadow-red-500/30">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

<script>
    // Intercept semua form dengan attribute [data-confirm]
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form[data-confirm]').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('confirm-open', {
                    detail: {
                        form: form,
                        message: form.dataset.confirm || 'Yakin ingin melanjutkan?'
                    }
                }));
            });
        });
    });
</script><?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/components/confirm-modal.blade.php ENDPATH**/ ?>