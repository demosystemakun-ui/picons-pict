@extends('layouts.app')

@section('title', 'Liquidations')

@section('content')
<div class="max-w-7xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Liquidations</h1>
            <p class="text-sm text-gray-500">List of liquidation forms.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Form No.</th>
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Requester</th>
                    <th class="px-4 py-3 font-medium">Department</th>
                    <th class="px-4 py-3 font-medium text-center">Status</th>
                    <th class="px-4 py-3 font-medium text-right">Total</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($liquidations as $lq)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $lq->number ?: '-' }}</td>
                    <td class="px-4 py-3">{{ $lq->request_date->format('d M Y') }}</td>
                    <td class="px-4 py-3">{{ $lq->requested_by }}</td>
                    <td class="px-4 py-3">{{ $lq->department }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($lq->status === 'approved')
                            <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Approved</span>
                        @else
                            <span class="rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Pending</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">{{ $lq->total_formatted }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1.5">
                            <a href="{{ route('liquidations.show', $lq) }}" class="rounded-md border px-2.5 py-1 text-xs hover:bg-gray-50">View</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">No liquidation forms yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $liquidations->links() }}</div>
</div>
@endsection