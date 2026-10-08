{{-- ═══════════════════════════════════════════════════════════════
     FLASH MODAL — Notifikasi mengambang di tengah layar
══════════════════════════════════════════════════════════════ --}}
@if (session('success') || session('error') || $errors->any())
<div x-data="{ show: true }"
     x-init="setTimeout(() => show = false, 5000)"
     x-show="show"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-90"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-90"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 pointer-events-none">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm pointer-events-auto"
         @click="show = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"></div>

    {{-- Card --}}
    <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl
                border border-gray-100 dark:border-gray-700
                pointer-events-auto overflow-hidden">

        {{-- Content --}}
        <div class="p-6">

            {{-- SUCCESS --}}
            @if (session('success'))
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/40
                                flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                        Berhasil!
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ session('success') }}
                    </p>
                </div>

            {{-- ERROR --}}
            @elseif (session('error') || $errors->any())
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/40
                                flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-red-600 dark:text-red-400"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                        Terjadi Kesalahan
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        {{ session('error') ?? 'Terdapat ' . $errors->count() . ' kesalahan pada form.' }}
                    </p>

                    @if ($errors->any())
                        <div class="w-full text-left rounded-lg bg-red-50 dark:bg-red-900/20
                                    border border-red-200 dark:border-red-800/60 p-3 mt-2 max-h-40 overflow-y-auto">
                            <ul class="list-disc list-inside space-y-1 text-xs text-red-700 dark:text-red-300">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

        </div>

        {{-- Close Button --}}
        <div class="px-6 pb-6">
            <button type="button"
                    @click="show = false"
                    class="w-full inline-flex items-center justify-center rounded-xl
                           bg-gray-900 hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100
                           text-white dark:text-gray-900 text-sm font-medium py-2.5
                           transition-colors">
                Tutup
            </button>
        </div>

        {{-- Progress bar (auto close) --}}
        <div class="h-1 bg-gray-100 dark:bg-gray-700">
            <div class="h-full bg-emerald-500 dark:bg-emerald-400"
                 x-init="setTimeout(() => $el.style.width = '0%', 50)"
                 style="width: 100%; transition: width 5s linear;"></div>
        </div>
    </div>
</div>
@endif