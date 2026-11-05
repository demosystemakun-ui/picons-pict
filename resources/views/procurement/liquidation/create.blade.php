@extends('layouts.app')

@section('title', 'Create Liquidation')

@section('content')
<div class="max-w-3xl mx-auto py-12">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h1 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Create Liquidation</h1>
        @if($procurement)
            <div class="mb-4 p-3 bg-indigo-50 border border-indigo-200 rounded-lg text-sm">
                <strong>Linked PR:</strong> {{ $procurement->no }} — {{ $procurement->description }}
            </div>
        @endif
        <form action="{{ route('liquidations.store') }}" method="POST">
            @csrf
            <input type="hidden" name="procurement_request_id" value="{{ $procurement->id ?? '' }}">
            
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Requester</label>
                    <input type="text" name="requested_by" value="{{ auth()->user()->name }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Request Date</label>
                    <input type="date" name="request_date" value="{{ date('Y-m-d') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                </div>
                <!-- Placeholder for items (will be added later) -->
                <input type="hidden" name="items[0][description]" value="Placeholder">
                <input type="hidden" name="items[0][qty]" value="1">
                <input type="hidden" name="items[0][unit]" value="Unit">
                <input type="hidden" name="items[0][price]" value="0">
            </div>
            
            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('procurement.show', $procurement->id ?? '') }}" class="px-4 py-2 border rounded-lg text-sm">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection