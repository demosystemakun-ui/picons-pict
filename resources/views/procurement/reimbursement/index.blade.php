@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Reimbursement</h1>
            <p class="text-sm text-gray-500">Daftar form klaim reimbursement.</p>
        </div>
        <a href="{{ route('reimbursement.create') }}"
           class="inline-flex items-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
            Buat form baru
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">No. form</th>
                        <th class="px-4 py-3 font-medium">Tanggal</th>
                        <th class="px-4 py-3 font-medium">Pemohon</th>
                        <th class="px-4 py-3 font-medium text-right">Item</th>
                        <th class="px-4 py-3 font-medium text-right">Total</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($reimbursements as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $r->number }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $r->request_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $r->requested_by }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ $r->items_count }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rp{{ number_format($r->items()->selectRaw('SUM(qty*price) t')->value('t'), 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1.5">
                                <a href="{{ route('reimbursement.show', $r) }}" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">Lihat</a>
                                <a href="{{ route('reimbursement.print', $r) }}" target="_blank" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">Cetak</a>
                                <a href="{{ route('reimbursement.pdf', $r) }}" target="_blank" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">PDF</a>
                                <a href="{{ route('reimbursement.edit', $r) }}" class="rounded-md bg-blue-700 px-2.5 py-1 text-xs text-white hover:bg-blue-800">Ubah</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">Belum ada form reimbursement. Klik "Buat form baru" untuk memulai.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $reimbursements->links() }}</div>
</div>
@endsection
