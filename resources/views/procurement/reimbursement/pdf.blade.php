<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Reimbursement {{ $reimbursement->number }}</title>
    <style>
        @page { margin: 28px 30px; }
        body { margin: 0; font-family: Helvetica, Arial, sans-serif; }
    </style>
</head>
<body>
    {{-- DomPDF butuh path file lokal untuk gambar --}}
@include('procurement.reimbursement._document', ['logo' => asset('logo/pict.png')])</body>
</html>
