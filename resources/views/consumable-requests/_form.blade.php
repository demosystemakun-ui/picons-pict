{{-- resources/views/consumable-requests/_form.blade.php --}}
<script>
    window.__UNIT_OPTIONS = @json($units ?? []);
</script>

<form id="consumableForm"
      method="POST"
      action="{{ route('consumable-requests.store') }}"
      enctype="multipart/form-data"
      class="space-y-5">
    @csrf

    {{-- SECTION 1: General Information --}}
    <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <header class="px-4 sm:px-5 py-3.5 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 text-gray-500 dark:text-gray-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white">General Information</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Basic details of the procurement request</p>
                </div>
            </div>
        </header>

        <div class="p-4 sm:p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="date" class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 tracking-wide uppercase">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                           class="w-full border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>
                <div>
                    <label for="subject" class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 tracking-wide uppercase">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                           placeholder="e.g. Power Tools for Operation Need"
                           class="w-full border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION 2: Item Request --}}
    <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <header class="px-4 sm:px-5 py-3.5 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="mt-0.5 text-purple-500 dark:text-purple-400 shrink-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white">Item List</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">Item details, purpose, and picture</p>
                    </div>
                </div>
                <button type="button" onclick="addItemRow()"
                        class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">Add Item</span>
                    <span class="sm:hidden">Item</span>
                </button>
            </div>
        </header>

        <div class="p-3 sm:p-4 space-y-4" id="itemsContainer"></div>

        <div class="p-4 sm:p-5 bg-gray-50/50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 space-y-4">
            <div class="p-3 sm:p-4 rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 text-center">
                <p class="text-[11px] sm:text-xs font-bold text-red-600 dark:text-red-400 uppercase leading-relaxed">
                    NOTE: IF YOU REPEAT ORDER THE GLOVES, MARKERS, BROOM, DUSTPAN, YOU MUST RETURN THE UNUSED ITEM<br>
                    (JIKA INGIN ORDER KEMBALI SEPERTI SARUNG TANGAN, SPIDOL, SAPU, PENGKI, HARUS MENYERAHKAN BEKAS YANG TIDAK TERPAKAI)
                </p>
            </div>

            <div>
                <label for="general_note" class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 tracking-wide uppercase">
                    Note
                </label>
                <input type="text" id="general_note" name="general_note" value="{{ old('general_note') }}"
                       placeholder="e.g. Request to Elsa"
                       class="w-full border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
            </div>
        </div>
    </section>

    {{-- SECTION 3: Signatures & Department --}}
    <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <header class="px-4 sm:px-5 py-3.5 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 text-emerald-500 dark:text-emerald-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white">Signatures & Department</h2>
                </div>
            </div>
        </header>

        <div class="p-4 sm:p-5 space-y-4">
            {{-- Department --}}
            <div>
                <label for="department" class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 tracking-wide uppercase">
                    Department <span class="text-red-500">*</span>
                </label>
                
                {{-- For PIC/User (Read-only) --}}
                @if(!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Admin Utama') && !auth()->user()->hasRole('Admin Departemen'))
                    <input type="text" id="department" name="department" 
                           value="{{ auth()->user()->department->name ?? '-' }}" readonly
                           class="w-full border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 text-sm px-3 py-2 cursor-not-allowed">
                
                {{-- For Admin (Selectable) --}}
                @else
                    <select id="department" name="department" required
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        <option value="">— Select Department —</option>
@foreach(($departments ?? []) as $dept)
                            <option value="{{ $dept }}" {{ old('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="request_by" class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 tracking-wide uppercase">
                        Request By <span class="text-red-500">*</span>
                    </label>
                    
                    {{-- For PIC/User (Read-only) --}}
                    @if(!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Admin Utama') && !auth()->user()->hasRole('Admin Departemen'))
                        <input type="text" id="request_by" name="request_by" 
                               value="{{ auth()->user()->name }}" readonly
                               class="w-full border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 text-sm px-3 py-2 cursor-not-allowed">
                    
                    {{-- For Admin (Inputable) --}}
                    @else
                        <input type="text" id="request_by" name="request_by" value="{{ old('request_by') }}" required
                               class="w-full border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                    @endif
                </div>
                <div>
                    <label for="approved_by" class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 tracking-wide uppercase">
                        Approved By
                    </label>
                    <input type="text" id="approved_by" name="approved_by" value="{{ old('approved_by') }}"
                           class="w-full border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>
                <div>
                    <label for="verified_by" class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1.5 tracking-wide uppercase">
                        Verified By
                    </label>
                    <input type="text" id="verified_by" name="verified_by" value="{{ old('verified_by') }}"
                           class="w-full border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION 4: Action Buttons --}}
    <div class="flex flex-col-reverse sm:flex-row gap-3 justify-end pt-2">
        <a href="{{ route('consumable-requests.my-index') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Cancel
        </a>
        <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Save Request
        </button>
    </div>
</form>