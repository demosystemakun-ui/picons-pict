@extends('layouts.app')

@section('content')
@php
    $in = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200';
    $lb = 'mb-1 block text-sm font-medium text-gray-700';
@endphp
<div class="space-y-4">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Signers</h1>
        <p class="text-sm text-gray-500">Daftar penandatangan yang bisa dipilih di form reimbursement.</p>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc space-y-0.5 pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Tambah --}}
    <form method="POST" action="{{ route('signers.store') }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        @csrf
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label class="{{ $lb }}">Nama</label>
                <input name="name" class="{{ $in }}" value="{{ old('name') }}" required>
            </div>
            <div>
                <label class="{{ $lb }}">Jabatan</label>
                <input name="title" class="{{ $in }}" value="{{ old('title') }}" required>
            </div>
            <div>
                <label class="{{ $lb }}">Posisi di form</label>
                <select name="slot" class="{{ $in }}" required>
                    @foreach(\App\Models\Signer::SLOTS as $k => $label)
                        <option value="{{ $k }}" @selected(old('slot') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300"> Aktif
                </label>
                <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">Tambah</button>
            </div>
        </div>
    </form>

    {{-- Daftar --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Jabatan</th>
                    <th class="px-4 py-3 font-medium">Posisi di form</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($signers as $s)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $s->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $s->title }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ \App\Models\Signer::SLOTS[$s->slot] ?? $s->slot }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $s->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1.5">
                            <a href="{{ route('signers.edit', $s) }}" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">Ubah</a>
                            <form method="POST" action="{{ route('signers.destroy', $s) }}" onsubmit="return confirm('Hapus penandatangan ini?')">
                                @csrf @method('DELETE')
                                <button class="rounded-md border border-red-300 px-2.5 py-1 text-xs text-red-700 hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada penandatangan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection