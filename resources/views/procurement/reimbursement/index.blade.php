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
                        <th class="px-4 py-3 font-medium text-center">Status</th> {{-- Tambahan Kolom Status --}}
                        <th class="px-4 py-3 font-medium text-right">Item</th>
                        <th class="px-4 py-3 font-medium text-right">Total</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($reimbursements as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $r->number ?: '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $r->request_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $r->requested_by }}</td>
                        
                        {{-- Tampilan Badge Status --}}
                        <td class="px-4 py-3 text-center">
                            @if($r->status === 'approved')
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Approved</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Pending</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right text-gray-600">{{ $r->items_count }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rp{{ number_format($r->items()->selectRaw('SUM(qty*price) t')->value('t'), 0, ',', '.') }}</td>
                        
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1.5 items-center">
                                
                                {{-- Tombol Khusus Admin: Jika Pending, Tampilkan Tombol Approve --}}
                                @php
                                    // Cek apakah user login adalah Admin atau Super Admin
                                    $isAdmin = auth()->check() && auth()->user()->hasAnyRole(['Super Admin', 'Admin']);
                                @endphp

                                @if($isAdmin && $r->status === 'pending')
                                    <button type="button" onclick="openApproveModal({{ $r->id }}, '{{ addslashes($r->number) }}')" 
                                            class="rounded-md bg-green-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-green-700">
                                        Approve
                                    </button>
                                @endif

                                <a href="{{ route('reimbursement.show', $r) }}" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">Lihat</a>
                                
                                {{-- Tombol Cetak & PDF (Bisa diakses semua user, atau sembunyikan jika belum approve) --}}
                                @if($r->status === 'approved')
                                    <a href="{{ route('reimbursement.print', $r) }}" target="_blank" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">Cetak</a>
                                    <a href="{{ route('reimbursement.pdf', $r) }}" target="_blank" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">PDF</a>
                                @endif
                                
                                <a href="{{ route('reimbursement.edit', $r) }}" class="rounded-md bg-blue-700 px-2.5 py-1 text-xs text-white hover:bg-blue-800">Ubah</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">Belum ada form reimbursement. Klik "Buat form baru" untuk memulai.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $reimbursements->links() }}</div>
</div>

{{-- MODAL UNTUK INPUT NOMOR SURAT SAAT APPROVE --}}
<div id="approveModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl mx-4">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Approve Reimbursement</h3>
        
        <form id="approveForm" method="POST" action="">
            @csrf
            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium text-gray-700">Nomor Surat (Manual)</label>
                <input type="text" name="number" id="inputNumber" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" required placeholder="Contoh: 001/OPS-PICT/CRF/XI/2026">
                <p class="mt-1 text-xs text-gray-500">Isi nomor surat resmi sebelum melakukan approve.</p>
            </div>
            
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeApproveModal()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">Konfirmasi Approve</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openApproveModal(id, currentNumber) {
        const modal = document.getElementById('approveModal');
        const form = document.getElementById('approveForm');
        const input = document.getElementById('inputNumber');

        // Set action URL form secara dinamis mengarah ke route approve
        form.action = `/reimbursement/${id}/approve`;
        
        // Isi input dengan nomor surat yang ada (jika ada), jika kosong biarkan kosong
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