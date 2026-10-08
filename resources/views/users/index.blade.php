@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Master Data - Users')

@section('content')
<div class="space-y-4">

    @if (session('success'))
        <div class="px-4 py-3 rounded-lg bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="px-4 py-3 rounded-lg bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email..."
                   class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 w-56">
            <select name="role" class="text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 px-3 py-2">
                <option value="">Semua Role</option>
                @foreach ($roles as $r)
                    <option value="{{ $r->name }}" @selected(request('role') == $r->name)>{{ $r->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="text-sm px-3 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-900">Filter</button>
        </form>

        <a href="{{ route('users.create') }}"
           class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah User
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="text-left px-5 py-3">Nama</th>
                        <th class="text-left px-5 py-3">Email</th>
                        <th class="text-left px-5 py-3">Departemen</th>
                        <th class="text-left px-5 py-3">Role</th>
                        <th class="text-right px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($users as $u)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-5 py-3 font-medium">{{ $u->name }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $u->email }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $u->department->name ?? '-' }}</td>
                            <td class="px-5 py-3">
                                @foreach ($u->roles as $role)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('users.edit', $u) }}" class="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    @if ($u->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $u) }}" method="POST" onsubmit="return confirm('Hapus user ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-md hover:bg-red-50 dark:hover:bg-red-900/30 text-red-500">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">Belum ada data user.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection