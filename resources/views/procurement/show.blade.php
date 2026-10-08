@extends('layouts.app')

@section('title', 'Detail Procurement Request')
@section('page-title', 'Detail Procurement Request')

@php
    /* ═══ STATUS CONFIG ═══ */
    $statusMeta = [
        'pending'  => ['label' => 'Menunggu',       'bg' => 'bg-amber-50 dark:bg-amber-900/30',   'text' => 'text-amber-700 dark:text-amber-300',   'ring' => 'ring-amber-200 dark:ring-amber-800/60',   'dot' => 'bg-amber-500'],
        'approved' => ['label' => 'Disetujui',      'bg' => 'bg-emerald-50 dark:bg-emerald-900/30','text' => 'text-emerald-700 dark:text-emerald-300','ring' => 'ring-emerald-200 dark:ring-emerald-800/60','dot' => 'bg-emerald-500'],
        'rejected' => ['label' => 'Ditolak',        'bg' => 'bg-red-50 dark:bg-red-900/30',        'text' => 'text-red-700 dark:text-red-300',        'ring' => 'ring-red-200 dark:ring-red-800/60',        'dot' => 'bg-red-500'],
        'revision' => ['label' => 'Perlu Revisi',   'bg' => 'bg-orange-50 dark:bg-orange-900/30',  'text' => 'text-orange-700 dark:text-orange-300',  'ring' => 'ring-orange-200 dark:ring-orange-800/60',  'dot' => 'bg-orange-500'],
    ];

    $currStatus = $procurementRequest->status ?? 'pending';
    $meta       = $statusMeta[$currStatus] ?? $statusMeta['pending'];

    /* ═══ ORDER TYPES ═══ */
    $orderTypes = array_filter([
        $procurementRequest->is_new_order    ? ['label' => 'New Order',    'class' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 ring-blue-200 dark:ring-blue-800/60']    : null,
        $procurementRequest->is_repeat_order ? ['label' => 'Repeat Order', 'class' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 ring-purple-200 dark:ring-purple-800/60'] : null,
        $procurementRequest->is_goods        ? ['label' => 'Goods',        'class' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 ring-emerald-200 dark:ring-emerald-800/60'] : null,
        $procurementRequest->is_services     ? ['label' => 'Services',     'class' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 ring-amber-200 dark:ring-amber-800/60']    : null,
    ]);

    /* ═══ APPROVAL CHAIN ═══ */
    $approvals = [
        ['label' => 'PIC',         'value' => $procurementRequest->pic_name],
        ['label' => 'Ops Manager', 'value' => $procurementRequest->ops_manager_name],
        ['label' => 'FEM Manager', 'value' => $procurementRequest->fem_manager_name],
        ['label' => 'COO',         'value' => $procurementRequest->coo_name],
    ];
@endphp

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="{{ route('procurement.index') }}"
               class="mt-0.5 inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition shrink-0"
               aria-label="Kembali ke daftar procurement">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-gray-900 dark:text-white font-mono">
                        {{ $procurementRequest->no }}
                    </h1>
                    <span class="inline-flex items-center gap-1.5 rounded-full {{ $meta['bg'] }} {{ $meta['text'] }} {{ $meta['ring'] }} ring-1 ring-inset px-2.5 py-0.5 text-xs font-semibold">
                        <span class="h-1.5 w-1.5 rounded-full {{ $meta['dot'] }} {{ $currStatus === 'pending' ? 'animate-pulse' : '' }}"></span>
                        {{ $meta['label'] }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Dibuat pada {{ $procurementRequest->created_at->format('d M Y, H:i') }} WIB
                </p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2">
            <form action="{{ route('procurement.status', $procurementRequest) }}" method="POST"
                  onsubmit="return confirm('Apakah Anda yakin ingin menyetujui pengajuan ini?')"
                  class="inline">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="approved">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Setujui
                </button>
            </form>

            <button type="button"
                    onclick="openActionModal('revision')"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                Minta Revisi
            </button>

            <button type="button"
                    onclick="openActionModal('rejected')"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition">
                <i data-lucide="x-circle" class="w-4 h-4"></i>
                Tolak
            </button>

            <span class="hidden sm:inline-block w-px h-6 bg-gray-200 dark:bg-gray-700 mx-1"></span>

            <a href="{{ route('procurement.print', $procurementRequest) }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i data-lucide="printer" class="w-4 h-4 text-blue-500"></i>
                Print
            </a>

            <a href="{{ route('procurement.pdf', $procurementRequest) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i data-lucide="download" class="w-4 h-4 text-emerald-500"></i>
                Unduh PDF
            </a>

            <a href="{{ route('procurement.edit', $procurementRequest) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg border border-amber-300 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition">
                <i data-lucide="pencil" class="w-4 h-4"></i>
                Edit
            </a>

            <form action="{{ route('procurement.destroy', $procurementRequest) }}" method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.')"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg border border-red-200 dark:border-red-900/60 bg-white dark:bg-gray-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    {{-- REVIEW NOTES ALERT --}}
    @if(!empty($procurementRequest->review_notes))
        @php
            $isRevision = $currStatus === 'revision';
            $alertStyle = $isRevision
                ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800/60'
                : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/60';
            $alertText  = $isRevision
                ? 'text-amber-800 dark:text-amber-300'
                : 'text-red-800 dark:text-red-300';
            $alertIcon  = $isRevision
                ? 'text-amber-500'
                : 'text-red-500';
        @endphp
        <div class="rounded-xl {{ $alertStyle }} border px-5 py-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 {{ $alertIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold {{ $alertText }}">
                        Catatan {{ $isRevision ? 'Revisi' : 'Penolakan' }}
                    </h3>
                    <p class="mt-1 text-sm {{ $alertText }} opacity-90 whitespace-pre-line leading-relaxed">
                        {{ $procurementRequest->review_notes }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- MAIN CARD --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700 overflow-hidden">

        {{-- SECTION 1: Informasi Umum --}}
        <section class="p-6">
            <header class="flex items-center gap-3 mb-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Informasi Umum</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Detail dasar pengajuan procurement</p>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1.5">Tanggal Permohonan</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $procurementRequest->date->format('d F Y') }}</span>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1.5">Divisi</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $procurementRequest->division }}</span>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1.5">Diajukan Oleh</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $procurementRequest->request_by }}</span>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1.5">Disiapkan Oleh</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $procurementRequest->prepared_by }}</span>
                </div>

                <div class="md:col-span-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1.5">Deskripsi Pengadaan</span>
                    <p class="font-medium text-gray-900 dark:text-white leading-relaxed">{{ $procurementRequest->description }}</p>
                </div>

                <div class="md:col-span-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-2">Tipe Pesanan</span>
                    <div class="flex flex-wrap gap-2">
                        @forelse($orderTypes as $type)
                            <span class="inline-flex items-center rounded-md {{ $type['class'] }} ring-1 ring-inset px-2.5 py-1 text-xs font-semibold">
                                {{ $type['label'] }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">— Tidak ada tipe pesanan —</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 2: Detail Kebutuhan & Justifikasi --}}
        <section class="p-6">
            <header class="flex items-center gap-3 mb-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Detail Kebutuhan &amp; Justifikasi</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Alasan dan spesifikasi yang diminta</p>
                </div>
            </header>

            <div class="space-y-5 text-sm">
                @php
                    $justifications = [
                        ['label' => 'Background',              'value' => $procurementRequest->background,      'pre' => false],
                        ['label' => 'Purpose',                 'value' => $procurementRequest->purpose,         'pre' => false],
                        ['label' => 'Required Specification',  'value' => $procurementRequest->required_spec,   'pre' => true],
                    ];
                @endphp

                @foreach($justifications as $j)
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1.5">
                            {{ $j['label'] }}
                        </span>
                        <p class="text-gray-900 dark:text-gray-200 leading-relaxed {{ $j['pre'] ? 'whitespace-pre-line' : '' }}">
                            {{ $j['value'] ?: '—' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- SECTION 3: Item Permintaan --}}
        <section class="p-6">
            <header class="flex items-center gap-3 mb-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Item Permintaan</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $procurementRequest->items->isNotEmpty() ? 'Hasil gabungan ' . $procurementRequest->items->count() . ' item dari Consumable Request' : 'Detail barang atau jasa yang diajukan' }}
                    </p>
                </div>
            </header>

            @if($procurementRequest->items->isNotEmpty())
                {{-- MULTI-ITEM --}}
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide w-12">No.</th>
                                <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Item</th>
                                <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide w-2/5">Catatan Kesesuaian</th>
                                <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide w-20 text-center">Qty</th>
                                <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide w-20 text-center">Unit</th>
                                <th class="py-3 px-4 text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide w-28">Gambar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($procurementRequest->items as $item)
                                <tr class="align-top hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                    <td class="py-3 px-4 text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>

                                    {{-- Kolom Item: format multiline --}}
                                    <td class="py-3 px-4">
                                        @php
                                            $lines = [];

                                            if (!empty($item->item_name)) $lines[] = $item->item_name;
                                            if (!empty($item->brand))     $lines[] = 'Brand : ' . $item->brand;
                                            if (!empty($item->type))      $lines[] = 'Type : ' . $item->type;
                                            if (!empty($item->model))     $lines[] = 'Model : ' . $item->model;
                                            if (!empty($item->capacity))  $lines[] = 'Capacity : ' . $item->capacity;

                                            if (!empty($item->specs)) {
                                                $specsLines = array_filter(array_map('trim', explode("\n", $item->specs)));
                                                $lines = array_merge($lines, $specsLines);
                                            }

                                            if (empty($lines) && !empty($item->item_details)) {
                                                $lines[] = $item->item_details;
                                            }
                                        @endphp

                                        <div class="text-sm font-medium text-gray-900 dark:text-white leading-relaxed space-y-0.5">
                                            @forelse($lines as $line)
                                                <p>{{ $line }}</p>
                                            @empty
                                                <span class="text-gray-400 italic">—</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    {{-- Catatan Kesesuaian --}}
                                    <td class="py-3 px-4 text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $item->item_requirement_note ?: '—' }}</td>

                                    {{-- Qty --}}
                                    <td class="py-3 px-4 text-center font-semibold text-gray-900 dark:text-white">{{ $item->quantity }}</td>

                                    {{-- Unit --}}
                                    <td class="py-3 px-4 text-center text-gray-600 dark:text-gray-300">{{ $item->unit ?: '—' }}</td>

                                    {{-- Gambar --}}
                                    <td class="py-3 px-4">
                                        @if($item->picture)
                                            <a href="{{ Storage::url($item->picture) }}" target="_blank" rel="noopener">
                                                <img src="{{ Storage::url($item->picture) }}" alt="Gambar item" class="h-12 w-12 object-cover rounded-md border border-gray-200 dark:border-gray-700">
                                            </a>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50/70 dark:bg-gray-900/40">
                            <tr>
                                <td colspan="3" class="py-3 px-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Qty</td>
                                <td class="py-3 px-4 text-center font-bold text-gray-900 dark:text-white">{{ $procurementRequest->items->sum('quantity') }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($procurementRequest->target_purchase)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-sm">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1.5">Target Pembelian</span>
                        <span class="text-gray-800 dark:text-gray-200">{{ $procurementRequest->target_purchase }}</span>
                    </div>
                @endif
            @else
                {{-- SINGLE ITEM --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-200 dark:border-gray-700 p-5 text-sm">
                    <div class="md:col-span-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1.5">Nama Item</span>
                        <div class="font-semibold text-gray-900 dark:text-white leading-relaxed space-y-0.5">
                            @php
                                $lines = [];
                                if (!empty($procurementRequest->item_name)) $lines[] = $procurementRequest->item_name;
                            @endphp
                            @forelse($lines as $line)
                                <p>{{ $line }}</p>
                            @empty
                                <span class="text-gray-400 italic">—</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1.5">Catatan Kesesuaian</span>
                        <span class="text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $procurementRequest->item_requirement_note ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1.5">Jumlah</span>
                        <span class="inline-flex items-center rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-2.5 py-1 font-semibold text-gray-900 dark:text-white">
                            {{ $procurementRequest->quantity }}
                        </span>
                    </div>
                    <div class="md:col-span-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1.5">Target Pembelian</span>
                        <span class="text-gray-800 dark:text-gray-200">{{ $procurementRequest->target_purchase ?: '—' }}</span>
                    </div>
                </div>
            @endif
        </section>

        {{-- SECTION 4: Approval Chain --}}
        <section class="p-6">
            <header class="flex items-center gap-3 mb-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Persetujuan &amp; Tanda Tangan</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Rantai persetujuan dokumen</p>
                </div>
            </header>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($approvals as $approval)
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center bg-gray-50/50 dark:bg-gray-900/30">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 block">
                            {{ $approval['label'] }}
                        </span>
                        <div class="mt-3 mb-2 h-px bg-gray-200 dark:bg-gray-700"></div>
                        <p class="font-semibold text-sm text-gray-800 dark:text-gray-200 truncate">
                            {{ $approval['value'] ?: '—' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>

    </div>
</div>

{{-- ACTION MODAL (Revisi / Tolak) --}}
<div id="actionModal"
     class="fixed inset-0 z-50 hidden"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modalTitle">

    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeActionModal()"></div>

    <div class="relative z-10 flex items-center justify-center min-h-full p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700">
            <div class="flex items-start gap-3 mb-4">
                <div id="modalIconWrap" class="flex h-10 w-10 items-center justify-center rounded-full shrink-0">
                    <svg id="modalIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 id="modalTitle" class="text-lg font-bold text-gray-900 dark:text-white tracking-tight">
                        Konfirmasi Aksi
                    </h3>
                    <p id="modalSubtitle" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Silakan masukkan alasan atau catatan terkait keputusan ini.
                    </p>
                </div>
            </div>

            <form action="{{ route('procurement.status', $procurementRequest) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" id="modalStatusInput" value="">

                <div class="mb-5">
                    <label for="review_notes" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                        Catatan / Alasan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="review_notes" id="review_notes" rows="4" required
                              placeholder="Tuliskan catatan detail..."
                              class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white p-3 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition resize-none"></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeActionModal()"
                            class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit" id="modalSubmitBtn"
                            class="px-4 py-2 text-sm font-semibold rounded-lg text-white shadow-sm transition">
                        Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    const MODAL_CONFIG = {
        revision: {
            title:      'Permintaan Revisi Pengadaan',
            subtitle:   'Berikan catatan agar pemohon dapat melakukan perbaikan.',
            btnText:    'Kirim Revisi',
            btnClass:   'px-4 py-2 text-sm font-semibold rounded-lg text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition',
            iconBg:     'bg-amber-100 dark:bg-amber-900/40',
            iconColor:  'text-amber-600 dark:text-amber-400',
            iconPath:   'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        },
        rejected: {
            title:      'Tolak Pengajuan Pengadaan',
            subtitle:   'Jelaskan alasan penolakan agar pemohon mendapat kejelasan.',
            btnText:    'Tolak Pengajuan',
            btnClass:   'px-4 py-2 text-sm font-semibold rounded-lg text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition',
            iconBg:     'bg-red-100 dark:bg-red-900/40',
            iconColor:  'text-red-600 dark:text-red-400',
            iconPath:   'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        },
    };

    const modal        = document.getElementById('actionModal');
    const titleEl      = document.getElementById('modalTitle');
    const subtitleEl   = document.getElementById('modalSubtitle');
    const statusInput  = document.getElementById('modalStatusInput');
    const submitBtn    = document.getElementById('modalSubmitBtn');
    const notesInput   = document.getElementById('review_notes');
    const iconWrap     = document.getElementById('modalIconWrap');
    const iconEl       = document.getElementById('modalIcon');

    function openActionModal(action) {
        const cfg = MODAL_CONFIG[action];
        if (!cfg || !modal) return;

        titleEl.textContent     = cfg.title;
        subtitleEl.textContent  = cfg.subtitle;
        statusInput.value       = action;
        submitBtn.textContent   = cfg.btnText;
        submitBtn.className     = cfg.btnClass;

        iconWrap.className = `flex h-10 w-10 items-center justify-center rounded-full shrink-0 ${cfg.iconBg} ${cfg.iconColor}`;
        iconEl.querySelector('path').setAttribute('d', cfg.iconPath);

        notesInput.value = '';

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        setTimeout(() => notesInput?.focus(), 100);
    }

    function closeActionModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    window.openActionModal  = openActionModal;
    window.closeActionModal = closeActionModal;

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeActionModal();
        }
    });
})();
</script>
@endpush
@endsection