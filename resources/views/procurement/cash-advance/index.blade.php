@extends('layouts.app')

@section('title', 'Cash Advances')

@section('content')
<div class="max-w-7xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Cash Advances</h1>
            <p class="text-sm text-gray-500">List of cash advance forms.</p>
        </div>
        <a href="{{ route('cash-advances.create') }}"
           class="inline-flex items-center rounded-lg bg-purple-700 px-4 py-2 text-sm font-medium text-white hover:bg-purple-800">
            Create New Form
        </a>
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
            @forelse($cashAdvances as $ca)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $ca->number ?: '-' }}</td>
                    <td class="px-4 py-3">{{ $ca->request_date->format('d M Y') }}</td>
                    <td class="px-4 py-3">{{ $ca->requested_by }}</td>
                    <td class="px-4 py-3">{{ $ca->department }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($ca->status === 'approved')
                            <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Approved</span>
                        @else
                            <span class="rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Pending</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">{{ $ca->total_formatted }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1.5">
                            {{-- Tombol Approve (jika Pending) --}}
                            @if($ca->status === 'pending')
                                <button type="button" onclick="openApproveModal({{ $ca->id }}, '{{ addslashes($ca->number) }}')" 
                                        class="rounded-md bg-green-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-green-700">
                                    Approve
                                </button>
                            @endif

                            <a href="{{ route('cash-advances.show', $ca) }}" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">View</a>
                            
                            @if($ca->status === 'approved')
                                <a href="{{ route('cash-advances.pdf', $ca) }}" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">PDF</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">No cash advance forms yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $cashAdvances->links() }}</div>
</div>

{{-- MODAL APPROVE (Input Nomor Surat) --}}
<div id="approveModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl mx-4">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Approve Cash Advance</h3>
        
        <form id="approveForm" method="POST" action="">
            @csrf
            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium text-gray-700">Official Number (Manual)</label>
                <input type="text" name="number" id="inputNumber" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500" required placeholder="e.g. 07/OPS-PICT/CAF/IV/2026">
                <p class="mt-1 text-xs text-gray-500">Enter the official number before approving.</p>
            </div>
            
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeApproveModal()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">Confirm Approve</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openApproveModal(id, currentNumber) {
        const modal = document.getElementById('approveModal');
        const form = document.getElementById('approveForm');
        const input = document.getElementById('inputNumber');

        // Set action URL form secara dinamis
        form.action = `/cash-advances/${id}/approve`;
        
        // Isi input dengan nomor surat (jika ada), jika kosong biarkan kosong
        input.value = currentNumber !== 'null' && currentNumber !== '-' ? currentNumber : '';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        input.focus();
    }

    function closeApproveModal() {
        const modal = document.getElementById('approveModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection