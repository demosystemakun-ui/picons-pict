@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Master Data - Edit User')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 max-w-3xl">
    <form action="{{ route('users.update', $user) }}" method="POST">
        @include('users._form')
    </form>
</div>
@endsection