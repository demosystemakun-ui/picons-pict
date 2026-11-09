<!-- @extends('layouts.app')

@section('title', 'All Vendor Comparisons')
@section('page-title', 'All Vendor Comparisons')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                All Vendor Comparisons
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Overview of all procurement requests with vendor comparisons.
            </p>
        </div>
    </div>

    {{-- TABLE --}}
    <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Procurement Requests with Vendors</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $procurements->total() }} procurement requests found</p>
        </header>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/50">
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">PR No.</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Division</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Description</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 text-center">Vendors</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 text-center">Status</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Winner</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($procurements as $procurement)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-5 py-4 font-mono font-semibold text-gray-900 dark:text-white">
                                {{ $procurement->no }}
                                @if($procurement->final_number)
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-0.5">
                                        Final: {{ $procurement->final_number }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $procurement->division }}</td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300 max-w-xs truncate">{{ $procurement->description }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center rounded-full bg-teal-50 dark:bg-teal-900/30 px-2.5 py-1 text-xs font-semibold text-teal-700 dark:text-teal-400">
                                    {{ $procurement->vendors->count() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    // PERBAIKAN: Menambahkan 'pending_final_sign' agar terdeteksi di halaman list
                                    $statusColors = [
                                        'approved'           => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                        'processed'          => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                        'pending_final_sign' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                        'final_approved'     => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                        'received'           => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                        'completed'          => 'bg-slate-50 text-slate-700 dark:bg-slate-900/30 dark:text-slate-400',
                                    ];
                                    $statusLabels = [
                                        'approved'           => 'Approved',
                                        'processed'          => 'PR Awal Validated',
                                        'pending_final_sign' => 'Pending Final Sign',
                                        'final_approved'     => 'Final Approved',
                                        'received'           => 'Received',
                                        'completed'          => 'Completed',
                                    ];
                                    $colorClass = $statusColors[$procurement->status] ?? 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
                                    $label = $statusLabels[$procurement->status] ?? ucfirst(str_replace('_', ' ', $procurement->status));
                                @endphp
                                <span class="inline-flex items-center rounded-md {{ $colorClass }} px-2.5 py-1 text-xs font-semibold">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if($procurement->winnerVendor)
                                    <div class="font-semibold text-emerald-700 dark:text-emerald-400">{{ $procurement->winnerVendor->vendor_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Rp {{ number_format($procurement->winnerVendor->total_price, 0, ',', '.') }}</div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Not selected</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('procurement.comparisons.index', $procurement) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-700 transition">
                                    View Comparison
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <p class="text-sm font-medium">No vendor comparisons yet</p>
                                    <p class="mt-1 text-xs">Start by creating a PR and performing vendor comparison.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($procurements->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                {{ $procurements->links() }}
            </div>
        @endif
    </section>
</div>
@endsection -->