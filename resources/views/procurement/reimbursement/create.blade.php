@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <h1 class="text-xl font-semibold text-gray-900">Buat form reimbursement</h1>
    <form method="POST" action="{{ route('reimbursement.store') }}" class="space-y-4">
        @include('procurement.reimbursement._form')
    </form>
</div>
@endsection
