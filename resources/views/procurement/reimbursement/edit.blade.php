@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <h1 class="text-xl font-semibold text-gray-900">Ubah form reimbursement</h1>
    <form method="POST" action="{{ route('reimbursement.update', $reimbursement) }}" class="space-y-4">
        @include('procurement.reimbursement._form', ['reimbursement' => $reimbursement])
    </form>
</div>
@endsection
