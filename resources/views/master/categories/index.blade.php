@extends('layouts.app')

@section('title', 'Categories')
@section('page-title', 'Master Data - Categories')

@section('content')
<div x-data="{ modalOpen: false, editing: null }" class="space-y-4">

    <div class="flex justify-end">
        <button @click="editing = null; modalOpen = true"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Kategori
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase">
                <tr>
                    <th class="text-left px-5 py-3">Kode</th>
                    <th class="text-left px-5 py-3">Nama</th>
                    <th class="text-right px-5 py-3">Jumlah Item</th>
                    <th class="text-center px-5 py-3">Status</th>
                    <th class="text-right px-5 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($categories as $cat)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3 font-medium">{{ $cat->code }}</td>
                        <td class="px-5 py-3">{{ $cat->name }}</td>
                        <td class="px-5 py-3 text-right text-gray-500">{{ $cat->items_count }}</td>
                        <td class="px-5 py-3 text-center">
                            @if ($cat->is_active)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">Active</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex justify-end gap-2">
                                <button @click='editing = @json($cat); modalOpen = true' class="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                <form action="{{ route('categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-md hover:bg-red-50 dark:hover:bg-red-900/30 text-red-500">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700">{{ $categories->links() }}</div>
    </div>

    <x-modal title="Kategori">
        <template x-if="true">
            <form :action="editing ? `/categories/${editing.id}` : '{{ route('categories.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <div>
                    <label class="block text-sm font-medium mb-1">Kode</label>
                    <input type="text" name="code" :value="editing ? editing.code : ''" required
                           class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Nama</label>
                    <input type="text" name="name" :value="editing ? editing.name : ''" required
                           class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" x-text="editing ? editing.description : ''"
                              class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 px-3 py-2"></textarea>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" :checked="editing ? editing.is_active : true">
                    Aktif
                </label>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </template>
    </x-modal>
</div>
@endsection
