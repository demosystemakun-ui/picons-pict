{{-- ═══════════════════════════════════════════════════════════════
     CONSOLIDATION MODAL
══════════════════════════════════════════════════════════════ --}}

@php
    $romanMonths = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
    $currentMonth = $romanMonths[(int) date('n') - 1];
    $currentYear  = date('Y');
    $defaultNo    = "/PR/OPS/PICT-{$currentMonth}-{$currentYear}";
@endphp

<div x-data="{
        open: false,
        prNumber: @js($defaultNo),
        background: '',
        purpose: '',
        error: '',

        submit() {
            if (!this.prNumber.trim()) {
                this.error = 'Nomor PR wajib diisi.';
                return;
            }
            this.error = '';
            window.dispatchEvent(new CustomEvent('pr-confirm', {
                detail: {
                    no: this.prNumber,
                    background: this.background,
                    purpose: this.purpose,
                }
            }));
            this.open = false;
        }
     }"
     x-on:pr-open.window="
        open = true;
        prNumber = @js($defaultNo);
        background = '';
        purpose = '';
        error = '';
     "
     x-show="open"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[110] flex items-center justify-center p-4">

    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="open = false"></div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl
                border border-gray-100 dark:border-gray-700 overflow-hidden max-h-[90vh] flex flex-col">

        {{-- Header --}}
        <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/40
                            flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Buat Purchase Requisition
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Lengkapi data PR. Field <b>Required spec</b> akan otomatis diambil dari detail item Consumable Request.
                    </p>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6 overflow-y-auto flex-1 space-y-5">

            {{-- Nomor PR --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                    Nomor PR <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       x-model="prNumber"
                       placeholder="/PR/OPS/PICT-IX-2026"
                       class="w-full border border-gray-300 dark:border-gray-700 rounded-lg
                              dark:bg-gray-900 dark:text-white text-sm px-3 py-2.5 font-mono
                              focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
            </div>

            {{-- Background --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                    Background
                    <span class="text-gray-400 font-normal normal-case">(Why you would like to purchase?)</span>
                </label>
                <textarea x-model="background" rows="4"
                          placeholder="Contoh: Berdasarkan lisensi terkait recertification (SIA) pada unit Tug Master yang telah expired..."
                          class="w-full border border-gray-300 dark:border-gray-700 rounded-lg
                                 dark:bg-gray-900 dark:text-white text-sm px-3 py-2.5
                                 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-y"></textarea>
            </div>

            {{-- Purpose --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                    Purpose
                    <span class="text-gray-400 font-normal normal-case">(What is the effect to purchase it?)</span>
                </label>
                <textarea x-model="purpose" rows="4"
                          placeholder="Contoh: Dengan adanya lisensi recertification (SIA) pada unit Tug Master sangat penting untuk memenuhi persyaratan safety..."
                          class="w-full border border-gray-300 dark:border-gray-700 rounded-lg
                                 dark:bg-gray-900 dark:text-white text-sm px-3 py-2.5
                                 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-y"></textarea>
            </div>

            {{-- Info Required Spec (read-only, hanya info) --}}
            <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/60">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wide mb-0.5">
                            Required Spec
                        </p>
                        <p class="text-xs text-blue-700 dark:text-blue-400/90 leading-relaxed">
                            Field ini akan <b>otomatis diisi</b> dari detail item Consumable Request yang digabungkan (Brand, Type, Model, Capacity, Specs).
                        </p>
                    </div>
                </div>
            </div>

            <p x-show="error" x-text="error" class="text-xs text-red-600 dark:text-red-400"></p>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center gap-3">
            <button type="button" @click="open = false"
                    class="flex-1 inline-flex items-center justify-center rounded-xl
                           border border-gray-200 dark:border-gray-600
                           bg-white dark:bg-gray-800
                           text-gray-700 dark:text-gray-300 text-sm font-medium py-2.5
                           hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Batal
            </button>
            <button type="button" @click="submit()"
                    class="flex-1 inline-flex items-center justify-center rounded-xl
                           bg-emerald-600 hover:bg-emerald-700
                           text-white text-sm font-medium py-2.5
                           transition-colors shadow-sm shadow-emerald-500/30">
                Lanjut Cetak
            </button>
        </div>
    </div>
</div>