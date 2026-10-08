@extends('layouts.app')

@section('title', 'Tambah Item')
@section('page-title', 'Master Data - Tambah Item')

@section('content')
<div class="max-w-2xl bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
    <form method="POST" action="{{ route('items.store') }}" class="space-y-4">
        @csrf
        @include('master.items._form')

        <div class="flex justify-end gap-2 pt-2">
            <a href="{{ route('items.index') }}" class="px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700">Batal</a>
            <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium">Simpan</button>
        </div>
    </form>
</div>
@endsection
