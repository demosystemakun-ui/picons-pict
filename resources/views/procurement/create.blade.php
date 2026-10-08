@extends('layouts.app')

@section('title', 'Buat Procurement Request')
@section('page-title', 'Procurement Request Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">

    {{-- Page Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Procurement Request
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Ajukan permintaan pengadaan barang atau jasa dengan detail lengkap dan terstruktur.
            </p>
        </div>
        <span class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-primary-50 dark:bg-primary-900/30 px-3 py-1 text-xs font-medium text-primary-700 dark:text-primary-300 ring-1 ring-inset ring-primary-200 dark:ring-primary-800">
            <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
            Draft Baru
        </span>
    </div>

    {{-- Error Alert --}}
    @if ($errors->any())
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/60 px-5 py-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">
                        Terdapat {{ $errors->count() }} kesalahan pada form
                    </h3>
                    <ul class="mt-2 list-disc list-inside space-y-0.5 text-sm text-red-700 dark:text-red-300/90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('procurement.store') }}" class="space-y-6">
        @csrf

        {{-- SECTION 1: Informasi Umum --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Informasi Umum</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Detail dasar permintaan pengadaan</p>
                    </div>
                </div>
            </header>

            <div class="p-6 space-y-5">
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label for="no" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                            No. Referensi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="no" name="no" value="{{ old('no', $suggestedNo) }}" required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                    </div>
                    <div>
                        <label for="date" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                    </div>
                </div>

                <div class="grid sm:grid-cols-3 gap-5">
                    <div>
                        <label for="request_by" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                            Request By <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="request_by" name="request_by" value="{{ old('request_by') }}" required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                    </div>
                    <div>
                        <label for="division" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                            Division <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="division" name="division" value="{{ old('division') }}" required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                    </div>
                    <div>
                        <label for="prepared_by" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                            Prepared By <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="prepared_by" name="prepared_by" value="{{ old('prepared_by') }}" required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="description" name="description" value="{{ old('description') }}" required
                           placeholder="Ringkasan singkat mengenai permintaan pengadaan ini"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">
                        Order Type <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach ([
                            'new_order'    => 'New Order',
                            'repeat_order' => 'Repeat Order',
                            'goods'        => 'Goods',
                            'services'     => 'Services',
                        ] as $value => $text)
                            <label class="relative flex cursor-pointer items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm transition hover:border-primary-400 hover:bg-primary-50/50 dark:hover:bg-primary-900/20 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:text-primary-700 dark:has-[:checked]:bg-primary-900/30 dark:has-[:checked]:text-primary-300 has-[:checked]:ring-2 has-[:checked]:ring-primary-500/20">
                                <input type="radio" name="order_type" value="{{ $value }}"
                                       {{ old('order_type', isset($procurementRequest) ? $procurementRequest->order_type : '') === $value ? 'checked' : '' }}
                                       class="sr-only">
                                {{ $text }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 2: Justification --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Justifikasi</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Alasan dan tujuan pengadaan</p>
                    </div>
                </div>
            </header>

            <div class="p-6 space-y-5">
                <div>
                    <label for="background" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                        Background
                    </label>
                    <textarea id="background" name="background" rows="3"
                              placeholder="Why you would like to purchase?"
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition resize-none">{{ old('background') }}</textarea>
                </div>

                <div>
                    <label for="purpose" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                        Purpose
                    </label>
                    <textarea id="purpose" name="purpose" rows="3"
                              placeholder="What the effect to purchase it? What if not purchase it?"
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition resize-none">{{ old('purpose') }}</textarea>
                </div>

                <div>
                    <label for="required_spec" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                        Required Spec
                    </label>
                    <textarea id="required_spec" name="required_spec" rows="4"
                              placeholder="Detail and reason of it"
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition resize-none">{{ old('required_spec') }}</textarea>
                </div>
            </div>
        </section>

        {{-- SECTION 3: Item Request --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Item Request</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Detail barang atau jasa yang diminta</p>
                    </div>
                </div>
            </header>

            <div class="p-6 space-y-5">
                <div class="grid sm:grid-cols-6 gap-5">
                    <div class="sm:col-span-3">
                        <label for="item_name" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                            Request Purchase Item <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="item_name" name="item_name" value="{{ old('item_name') }}" required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="item_requirement_note" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                            Match Requirement?
                        </label>
                        <input type="text" id="item_requirement_note" name="item_requirement_note" value="{{ old('item_requirement_note') }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                    </div>
                    <div class="sm:col-span-1">
                        <label for="quantity" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                            Qty <span class="text-red-500">*</span>
                        </label>
                        <input type="number" min="1" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                    </div>
                </div>

                <div>
                    <label for="target_purchase" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                        Target to Purchase
                    </label>
                    <textarea id="target_purchase" name="target_purchase" rows="2"
                              placeholder="Consider the install schedule"
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition resize-none">{{ old('target_purchase') }}</textarea>
                </div>
            </div>
        </section>

        {{-- SECTION 4: Approval Chain --}}
        <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Approval Chain</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Rantai persetujuan dokumen</p>
                    </div>
                </div>
            </header>

            <div class="p-6">
                <div class="grid sm:grid-cols-4 gap-5">
                    @foreach ([
                        ['name' => 'pic_name', 'label' => 'PIC'],
                        ['name' => 'ops_manager_name', 'label' => 'Ops Manager'],
                        ['name' => 'fem_manager_name', 'label' => 'FEM Manager'],
                        ['name' => 'coo_name', 'label' => 'COO'],
                    ] as $field)
                        <div>
                            <label for="{{ $field['name'] }}" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                                {{ $field['label'] }}
                            </label>
                            <input type="text" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ old($field['name']) }}"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition">
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Action Bar --}}
        <div class="sticky bottom-4 z-10">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-800/90 backdrop-blur px-5 py-3.5 shadow-lg">
                <p class="text-xs text-gray-500 dark:text-gray-400 order-2 sm:order-1">
                    Pastikan seluruh data sudah benar sebelum menyimpan.
                </p>
                <div class="flex items-center gap-3 order-1 sm:order-2 w-full sm:w-auto">
                    <a href="{{ url()->previous() }}"
                       class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Request
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection