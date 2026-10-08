<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Reimbursement {{ $reimbursement->number }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        body { margin: 0; background: #eee; }
        .sheet { width: 186mm; min-height: 273mm; margin: 10mm auto; padding: 0; background: #fff; }
        .crf, .crf table { font-size: 9.5px !important; }
        .toolbar { text-align: center; padding: 10px; font-family: Arial, sans-serif; }
        .toolbar button, .toolbar a { padding: 6px 14px; margin: 0 4px; cursor: pointer; }
        @media print {
            body { background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .toolbar { display: none; }
            .sheet { margin: 0; width: auto; min-height: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">Cetak</button>
        <a href="{{ route('reimbursement.pdf', $reimbursement) }}">Unduh PDF</a>
        <a href="{{ route('reimbursement.show', $reimbursement) }}">Kembali</a>
    </div>

    <div class="sheet">
@include('procurement.reimbursement._document', ['logo' => asset('logo/pict.png')])    </div>

    <script>window.addEventListener('load', () => { if (location.search.includes('auto')) window.print(); });</script>
</body>
</html>
