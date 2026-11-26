{{-- resources/views/signers/edit.blade.php --}}
@php
    $signerName   = old('name', $signer->name ?? $signer->nama ?? '');
    $signerTitle  = old('title', $signer->title ?? $signer->role ?? $signer->jabatan ?? '');
    $signerSlot   = old('slot', $signer->slot ?? $signer->position ?? $signer->form_position ?? 'Checked');
    $signerActive = old('is_active', isset($signer->is_active) ? (int) $signer->is_active : (isset($signer->status) ? ($signer->status === 'active' || $signer->status == 1 ? 1 : 0) : 1));

    $slotOptions = ['Checked', 'Verified', 'Approved', 'Acknowledge'];
    if ($signerSlot && !in_array($signerSlot, $slotOptions, true)) {
        $slotOptions[] = $signerSlot;
    }
@endphp

@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Signer</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Update signatory details (Signer #{{ $signer->id }})
                </p>
            </div>

            <a href="{{ route('signers.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Back
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                <div class="font-semibold mb-1">Please fix the following errors:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <form action="{{ route('signers.update', $signer) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ $signerName }}"
                           required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="title"
                               name="title"
                               value="{{ $signerTitle }}"
                               placeholder="e.g. CFO, Accounting MGR, COO"
                               required
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                    </div>

                    <div>
                        <label for="slot" class="block text-sm font-medium text-gray-700 mb-1">
                            Form Position <span class="text-red-500">*</span>
                        </label>
                        <select id="slot"
                                name="slot"
                                required
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                            @foreach ($slotOptions as $opt)
                                <option value="{{ $opt }}" @selected($signerSlot === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">
                        Status
                    </label>
                    <select id="is_active"
                            name="is_active"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                        <option value="1" @selected((string) $signerActive === '1')>Active</option>
                        <option value="0" @selected((string) $signerActive === '0')>Inactive</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <a href="{{ route('signers.index') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-5 py-2 text-sm font-semibold text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
