<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Konsolidasi Consumable Request ke PR</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; border: 1px solid #000; padding: 0; background: #fff; }
        .header { display: flex; align-items: center; border-bottom: 2px solid #000; padding: 10px 15px; }
        .header .title { flex-grow: 1; text-align: center; }
        .header h2 { margin: 0; font-size: 15px; text-transform: uppercase; letter-spacing: 1px; }
        .info { padding: 10px 15px; border-bottom: 1px solid #000; font-size: 12px; }
        .table-items { width: 100%; border-collapse: collapse; font-size: 11px; }
        .table-items th, .table-items td { border: 1px solid #000; padding: 6px; vertical-align: top; }
        .table-items th { background: #f0f0f0; text-align: center; }
        .no-print { margin-bottom: 20px; text-align: right; }
        .no-print button { padding: 8px 15px; background: #0d9488; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="no-print">
    <button onclick="window.print()">Cetak Rekap PR</button>
</div>

<div class="container">
    <div class="header">
        <div class="logo">
            <img src="{{ asset('logo/pict.png') }}" alt="Logo" style="max-height: 40px;">
        </div>
        <div class="title">
            <h2>REKAP KONSOLIDASI PURCHASE REQUISITION (CONSUMABLE)</h2>
        </div>
    </div>

    <div class="info">
        <strong>Tanggal Rekap:</strong> {{ date('d/m/Y') }}<br>
        <strong>Gabungan Request ID:</strong> 
        @foreach($selectedRequests as $req)
            #{{ $req->id }}{{ !$loop->last ? ', ' : '' }}
        @endforeach
    </div>

    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 30px;">No.</th>
                <th style="width: 80px;">Req ID</th>
                <th>Item & Specs / Details</th>
                <th style="width: 30%;">Purpose</th>
                <th style="width: 50px;">Qty</th>
                <th style="width: 50px;">Unit</th>
                <th>Request By</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($selectedRequests as $req)
                @foreach($req->items as $item)
                    <tr>
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td style="text-align: center;">#{{ $req->id }}</td>
                        <td>{{ $item->item_details }}</td>
                        <td style="white-space: pre-line;">
                            1. {{ $item->purpose_1 }}<br>
                            2. {{ $item->purpose_2 }}<br>
                            3. {{ $item->purpose_3 }}
                        </td>
                        <td style="text-align: center;">{{ intval($item->quantity) }}</td>
                        <td style="text-align: center;">{{ $item->unit }}</td>
                        <td>{{ $req->request_by }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>

</body>
</html>