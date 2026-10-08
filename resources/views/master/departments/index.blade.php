@extends('layouts.app')

@section('title', 'Departments')

@section('content')
@php
    $in   = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200';
    $lb   = 'mb-1 block text-sm font-medium text-gray-700';
    $card = 'rounded-xl border border-gray-200 bg-white shadow-sm';
@endphp

<div class="space-y-4">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Departments</h1>
            <p class="text-sm text-gray-500">Manage master data for departments.</p>
        </div>
        <button type="button" id="btn-add"
                class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
            + Add Department
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc space-y-0.5 pl-5">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="{{ $card }} p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div class="md:col-span-2">
                <label class="{{ $lb }}">Search</label>
                <input name="q" value="{{ request('q') }}" class="{{ $in }}"
                       placeholder="Code, name, or cost center...">
            </div>
            <div>
                <label class="{{ $lb }}">Status</label>
                <select name="status" class="{{ $in }}">
                    <option value="">All</option>
                    <option value="active"   @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900">
                    Filter
                </button>
                <a href="{{ route('departments.index') }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="{{ $card }} overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr>
                        <th class="px-4 py-3 font-medium" style="width:60px">#</th>
                        <th class="px-4 py-3 font-medium" style="min-width:100px">Code</th>
                        <th class="px-4 py-3 font-medium" style="min-width:220px">Name</th>
                        <th class="px-4 py-3 font-medium" style="min-width:140px">Cost Center</th>
                        <th class="px-4 py-3 font-medium text-center" style="min-width:110px">Users</th>
                        <th class="px-4 py-3 font-medium text-center" style="min-width:110px">Status</th>
                        <th class="px-4 py-3 font-medium text-right" style="min-width:160px">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($departments as $i => $dept)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">
                                {{ $departments->firstItem() + $i }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs bg-gray-100 rounded px-2 py-0.5 text-gray-800">
                                    {{ $dept->code }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $dept->name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $dept->cost_center ?: '—' }}</td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $dept->users_count }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($dept->is_active)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button"
                                        class="js-edit rounded-md border border-blue-300 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50"
                                        data-id="{{ $dept->id }}"
                                        data-code="{{ $dept->code }}"
                                        data-name="{{ $dept->name }}"
                                        data-cost_center="{{ $dept->cost_center }}"
                                        data-is_active="{{ $dept->is_active ? 1 : 0 }}">
                                    Edit
                                </button>
                                <form action="{{ route('departments.destroy', $dept) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Delete this department?');">
                                    @csrf @method('DELETE')
                                    <button class="rounded-md border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                No departments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($departments->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $departments->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ============ MODAL FORM ============ --}}
<div id="modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close></div>
    <div class="relative mx-auto mt-20 w-full max-w-lg">
        <div class="rounded-xl border border-gray-200 bg-white shadow-lg">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3">
                <h2 id="modal-title" class="text-sm font-semibold text-gray-800">Add Department</h2>
                <button type="button" class="text-gray-400 hover:text-gray-700" data-close>&times;</button>
            </div>

            <form id="modal-form" method="POST" action="{{ route('departments.store') }}">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">

                <div class="space-y-4 p-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="{{ $lb }}">Code <span class="text-red-500">*</span></label>
                            <input name="code" id="f-code" class="{{ $in }}" required maxlength="20"
                                   placeholder="e.g. IT, HRD, FIN">
                        </div>
                        <div>
                            <label class="{{ $lb }}">Cost Center</label>
                            <input name="cost_center" id="f-cost_center" class="{{ $in }}" maxlength="50"
                                   placeholder="e.g. CC-001">
                        </div>
                    </div>
                    <div>
                        <label class="{{ $lb }}">Name <span class="text-red-500">*</span></label>
                        <input name="name" id="f-name" class="{{ $in }}" required maxlength="255"
                               placeholder="e.g. Information Technology">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="f-is_active" value="1"
                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                        <label for="f-is_active" class="text-sm text-gray-700">Active</label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-200 px-5 py-3">
                    <button type="button" data-close
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const modal     = document.getElementById('modal');
    const form      = document.getElementById('modal-form');
    const title     = document.getElementById('modal-title');
    const method    = document.getElementById('form-method');
    const fCode     = document.getElementById('f-code');
    const fName     = document.getElementById('f-name');
    const fCC       = document.getElementById('f-cost_center');
    const fActive   = document.getElementById('f-is_active');

    const storeUrl  = @json(route('departments.store'));
    const updateTpl = @json(route('departments.update', ['department' => '__ID__']));

    function openModal()  { modal.classList.remove('hidden'); }
    function closeModal() { modal.classList.add('hidden'); }

    function resetForm() {
        form.action     = storeUrl;
        method.value    = 'POST';
        fCode.value     = '';
        fName.value     = '';
        fCC.value       = '';
        fActive.checked = true;
        title.textContent = 'Add Department';
    }

    document.getElementById('btn-add').addEventListener('click', () => {
        resetForm();
        openModal();
        fCode.focus();
    });

    modal.addEventListener('click', e => {
        if (e.target.matches('[data-close]')) closeModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    document.querySelectorAll('.js-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            resetForm();
            form.action     = updateTpl.replace('__ID__', d.id);
            method.value    = 'PUT';
            fCode.value     = d.code || '';
            fName.value     = d.name || '';
            fCC.value       = d.cost_center || '';
            fActive.checked = d.is_active === '1';
            title.textContent = 'Edit Department';
            openModal();
            fCode.focus();
        });
    });

    @if($errors->any())
        openModal();
    @endif
})();
</script>
@endsection