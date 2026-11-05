@extends('layouts.app')

@section('title', 'Cash Advance Detail')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-24">

    {{-- ACTION TOOLBAR (Disembunyikan saat print) --}}
    <div class="flex flex-wrap items-center justify-between gap-3 no-print">
        <h1 class="text-xl font-semibold text-gray-900">{{ $cashAdvance->number }}</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('cash-advances.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Back</a>
            <button onclick="window.print()" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Print</button>
            <a href="{{ route('cash-advances.pdf', $cashAdvance) }}" target="_blank" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Download PDF</a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 no-print">{{ session('success') }}</div>
    @endif

    {{-- AREA DOKUMEN --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white p-6 shadow-sm document-card">
        <div class="mx-auto" style="min-width:720px; max-width:820px;">
            @include('procurement.cash-advance._document', ['logo' => asset('logo/pict.png')])
        </div>
    </div>
</div>

<style>
    @media print {
        body { background: #fff !important; }
        header, nav, aside, footer, .no-print { display: none !important; }
        .document-card { overflow: visible !important; border: none !important; box-shadow: none !important; padding: 0 !important; background: #fff !important; }
    }
</style>
@endsection