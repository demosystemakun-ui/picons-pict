@extends('layouts.app')

@section('title', 'Detail Cash Advance')
@section('page-title', 'Detail Cash Advance')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <h1 class="text-xl font-bold mb-4">Detail Cash Advance #{{ $cashAdvance->number ?? $cashAdvance->id }}</h1>
        
        <div class="grid grid-cols-2 gap-4 text-sm mb-6">
            <div>
                <p class="text-gray-500">Requester</p>
                <p class="font-medium">{{ $cashAdvance->requested_by ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Department</p>
                <p class="font-medium">{{ $cashAdvance->department ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Total</p>
                <p class="font-bold text-emerald-600">{{ $cashAdvance->total_formatted ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Status</p>
                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                    {{ strtoupper($cashAdvance->status) }}
                </span>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-between">
            <a href="{{ route('cash-advances.index') }}" class="text-sm text-gray-500 hover:underline">Back to List</a>
            @if($cashAdvance->status === 'approved')
                <a href="{{ route('cash-advances.pdf', $cashAdvance) }}" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700">Print PDF</a>
            @endif
        </div>
    </div>
</div>
@endsection