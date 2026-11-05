@extends('layouts.app')

@section('title', 'Consumable Request Consolidation')
@section('page-title', 'Consolidate Requests into PR')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-24">

    {{-- ERROR ALERT --}}
    @if (session('error'))
        <div class="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- SUCCESS ALERT --}}
    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('consumable-requests.consolidation.process') }}" method="POST" class="space-y-6">
        @csrf
        
        {{-- SECTION 1: NEW PR INFORMATION --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4">New Procurement Request (PR) Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">PR Number <span class="text-red-500">*</span></label>
                    <input type="text" name="no" value="{{ old('no', \App\Models\ProcurementRequest::generateNumber()) }}" required
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:ring-primary-500 focus:border-primary-500 @error('no') border-red-500 @enderror">
                    @error('no') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Target Purchase Date <span class="text-red-500">*</span></label>
                    <input type="date" name="target_purchase" value="{{ old('target_purchase', now()->addDays(7)->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:ring-primary-500 focus:border-primary-500 @error('target_purchase') border-red-500 @enderror">
                    @error('target_purchase') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Background</label>
                    <textarea name="background" rows="2" placeholder="Optional, e.g., Operational needs for this month..."
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:ring-primary-500 focus:border-primary-500">{{ old('background') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Purpose</label>
                    <textarea name="purpose" rows="2" placeholder="Optional, e.g., To support operational activities..."
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:ring-primary-500 focus:border-primary-500">{{ old('purpose') }}</textarea>
                </div>
            </div>
        </div>

        {{-- SECTION 2: SELECT CONSUMABLE REQUESTS --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-800 dark:text-white">Select Approved Consumable Requests</h2>
                <p class="text-xs text-gray-500 mt-0.5">Check the requests below to consolidate them into a PR.</p>
            </div>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium shadow-sm transition">
                Merge into PR
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </th>
                            <th class="py-3 px-4 text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">ID / Date</th>
                            <th class="py-3 px-4 text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Subject</th>
                            <th class="py-3 px-4 text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Request By</th>
                            <th class="py-3 px-4 text-xs font-semibold uppercase text-gray-600 dark:text-gray-400">Total Items</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($approvedRequests as $req)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                <td class="py-3 px-4 text-center">
                                    <input type="checkbox" name="request_ids[]" value="{{ $req->id }}" class="request-checkbox rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-gray-800 dark:text-white">#{{ $req->id }}</span>
                                    <span class="block text-xs text-gray-400">{{ \Carbon\Carbon::parse($req->date)->format('d/m/Y') }}</span>
                                </td>
                                <td class="py-3 px-4 font-medium text-gray-800 dark:text-white">{{ $req->subject }}</td>
                                <td class="py-3 px-4 text-gray-600 dark:text-gray-300">{{ $req->request_by }}</td>
                                <td class="py-3 px-4 text-gray-600 dark:text-gray-300">{{ $req->items->count() }} Item(s)</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-400 text-sm">
                                    There are no approved Consumable Requests that haven't been consolidated yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.request-checkbox').forEach(cb => cb.checked = this.checked);
    });
</script>
@endpush
@endsection