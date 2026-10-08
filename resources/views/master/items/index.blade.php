@extends('layouts.app')

@section('title', 'Items')
@section('page-title', 'Master Data - Items')

@section('content')
<div x-data="{ modalOpen: false, editItem: null }" class="space-y-4">

    {{-- Header actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari item..."
                       class="pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:ring-blue-500 focus:border-blue-500 w-56">
            </div>
            <select name="category_id" class="text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 px-3 py-2">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-1.5 text-sm px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700">
                <input type="checkbox" name="low_stock" value="1" @checked(request('low_stock'))>
                Low Stock
            </label>
            <button type="submit" class="text-sm px-3 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-900">Filter</button>
        </form>

        <button @click="editItem = null; modalOpen = true"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Item
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="text-left px-5 py-3">Kode</th>
                        <th class="text-left px-5 py-3">Nama Barang</th>
                        <th class="text-left px-5 py-3">Kategori</th>
                        <th class="text-left px-5 py-3">Satuan</th>
                        <th class="text-right px-5 py-3">Stock</th>
                        <th class="text-right px-5 py-3">Min. Stock</th>
                        <th class="text-center px-5 py-3">Status</th>
                        <th class="text-right px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($items as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-5 py-3 font-medium">{{ $item->item_code }}</td>
                            <td class="px-5 py-3">{{ $item->item_name }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $item->category->name }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $item->unit->name }}</td>
                            <td class="px-5 py-3 text-right {{ $item->isLowStock() ? 'text-red-600 font-semibold' : '' }}">
                                {{ $item->current_stock }}
                            </td>
                            <td class="px-5 py-3 text-right text-gray-500">{{ $item->minimum_stock }}</td>
                            <td class="px-5 py-3 text-center">
                                @if ($item->status === 'active')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('items.edit', $item) }}" class="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('items.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus item ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-md hover:bg-red-50 dark:hover:bg-red-900/30 text-red-500">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-gray-400">Belum ada data item.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700">
            {{ $items->links() }}
        </div>
    </div>
</div>

<p class="text-xs text-gray-400 mt-4">
    Catatan: form Tambah/Edit Item menggunakan halaman terpisah ({{ route('items.create') }}) agar validasi & upload lebih mudah dikembangkan.
    Modul lain (Category, Unit, Vendor, Department) memakai modal on-page — lihat halaman masing-masing.
</p>
@endsection
