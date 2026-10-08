@extends('layouts.app')

@section('title', 'Edit Item')
@section('page-title', 'Master Data - Edit Item')

@section('content')
<div class="max-w-2xl bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
    <form method="POST" action="{{ route('items.update', $item) }}" class="space-y-4">
        @csrf
        @method('PUT')
        @include('master.items._form')

        <div class="flex justify-end gap-2 pt-2">
            <a href="{{ route('items.index') }}" class="px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700">Batal</a>
            <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium">Update</button>
        </div>
    </form>
</div>
@endsection
