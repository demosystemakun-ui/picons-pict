{{-- resources/views/consumable-requests/_form.blade.php --}}
<script>
    window.__UNIT_OPTIONS = @json($units ?? []);
</script>

<form id="consumableForm"
      method="POST"
      action="{{ route('consumable-requests.store') }}"
      enctype="multipart/form-data"
      class="space-y-6">
    @csrf

    {{-- SECTION 1: General Information --}}
    <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">General Information</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Basic details of the procurement request</p>
        </div>

        <div class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-5">
                <div class="sm:col-span-4">
                    <label for="date" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                           class="w-full h-9 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                </div>
                <div class="sm:col-span-8">
                    <label for="subject" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                           placeholder="e.g. Power Tools for Operation Need"
                           class="w-full h-9 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION 2: Item Request --}}
    <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Item List</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Item details, purpose, and picture</p>
            </div>
            <button type="button" onclick="addItemRow()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors shadow-sm">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add Item
            </button>
        </div>

        {{-- Container untuk baris-baris item yang di-generate JS --}}
        <div class="p-5 space-y-4 bg-gray-50/50 dark:bg-gray-900/20" id="itemsContainer"></div>

        <div class="p-5 space-y-5">
            {{-- Alert Note yang lebih rapi --}}
            <div class="flex items-start gap-3 p-3.5 rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20">
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                    <span class="font-bold">PENTING:</span> Jika ingin order kembali (seperti sarung tangan, spidol, sapu, pengki), Anda <span class="font-semibold underline">HARUS MENYERAHKAN BEKAS</span> yang tidak terpakai.<br>
                    <span class="italic text-amber-700 dark:text-amber-400">(NOTE: IF YOU REPEAT ORDER FOR GLOVES, MARKERS, BROOM, DUSTPAN, YOU MUST RETURN THE UNUSED ITEM)</span>
                </div>
            </div>

            <div>
                <label for="general_note" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    General Note
                </label>
                <input type="text" id="general_note" name="general_note" value="{{ old('general_note') }}"
                       placeholder="e.g. Request to Elsa"
                       class="w-full h-9 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
            </div>
        </div>
    </section>

    {{-- SECTION 3: Signatures & Department --}}
    <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Signatures & Department</h2>
        </div>

        <div class="p-5 space-y-5">
            {{-- Department --}}
            <div class="max-w-md">
                <label for="department" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Department <span class="text-red-500">*</span>
                </label>
                
                {{-- For PIC/User (Read-only) --}}
                @if(!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Admin Utama') && !auth()->user()->hasRole('Admin Departemen'))
                    <input type="text" id="department" name="department" 
                           value="{{ auth()->user()->department->name ?? '-' }}" readonly
                           class="w-full h-9 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-sm px-3 cursor-not-allowed">
                
                {{-- For Admin (Selectable) --}}
                @else
                    <select id="department" name="department" required
                            class="w-full h-9 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                        <option value="">— Select Department —</option>
                        @foreach(($departments ?? []) as $dept)
                            <option value="{{ $dept }}" {{ old('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label for="request_by" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Request By <span class="text-red-500">*</span>
                    </label>
                    
                    {{-- For PIC/User (Read-only) --}}
                    @if(!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Admin Utama') && !auth()->user()->hasRole('Admin Departemen'))
                        <input type="text" id="request_by" name="request_by" 
                               value="{{ auth()->user()->name }}" readonly
                               class="w-full h-9 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-sm px-3 cursor-not-allowed">
                    
                    {{-- For Admin (Inputable) --}}
                    @else
                        <input type="text" id="request_by" name="request_by" value="{{ old('request_by') }}" required
                               class="w-full h-9 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                    @endif
                </div>
                <div>
                    <label for="approved_by" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Approved By
                    </label>
                    <input type="text" id="approved_by" name="approved_by" value="{{ old('approved_by') }}"
                           class="w-full h-9 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                </div>
                <div>
                    <label for="verified_by" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Verified By
                    </label>
                    <input type="text" id="verified_by" name="verified_by" value="{{ old('verified_by') }}"
                           class="w-full h-9 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
                </div>
            </div>
        </div>
    </section>
</form>