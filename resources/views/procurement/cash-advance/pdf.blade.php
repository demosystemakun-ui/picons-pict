<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Advance - {{ $cashAdvance->number ?? $cashAdvance->id }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; font-size: 14px; }
        
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 6px; vertical-align: top; }
        .info-table td.label { font-weight: bold; width: 150px; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; text-align: center; }
        .items-table td.text-right { text-align: right; }
        .items-table td.text-center { text-align: center; }
        
        .total-row { font-weight: bold; background-color: #e6e6e6; }
        
        .signatures { width: 100%; margin-top: 50px; text-align: center; border-collapse: collapse; }
        .signatures td { width: 25%; padding-top: 60px; }
        .signatures .line { border-top: 1px solid #000; margin: 0 20px; padding-top: 5px; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <h1>Cash Advance Form</h1>
        <p>No: {{ $cashAdvance->number ?? 'DRAFT' }}</p>
    </div>

    {{-- INFO --}}
    <table class="info-table">
        <tr>
            <td class="label">Date</td>
            <td>: {{ \Carbon\Carbon::parse($cashAdvance->request_date)->format('d F Y') }}</td>
            <td class="label">Department</td>
            <td>: {{ $cashAdvance->department }}</td>
        </tr>
        <tr>
            <td class="label">Requested By</td>
            <td>: {{ $cashAdvance->requested_by }}</td>
            <td class="label">Position</td>
            <td>: {{ $cashAdvance->position ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Bank Name</td>
            <td>: {{ $cashAdvance->bank_name ?? '-' }}</td>
            <td class="label">Account No.</td>
            <td>: {{ $cashAdvance->account_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Delivery Address</td>
            <td colspan="3">: {{ $cashAdvance->delivery_address ?? '-' }}</td>
        </tr>
    </table>

    {{-- ITEMS --}}
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="40%">Item Description</th>
                <th width="25%">Purpose</th>
                <th width="10%">Qty</th>
                <th width="10%">Unit</th>
                <th width="10%">Price</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($cashAdvance->items as $index => $item)
                @php $subtotal = $item->qty * $item->price; $total += $subtotal; @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->item_description }}</td>
                    <td>{{ $item->purpose ?? '-' }}</td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-center">{{ $item->unit }}</td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL</td>
                <td class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- SIGNATURES --}}
    <table class="signatures">
        <tr>
            <td>
                <div>Requested By,</div>
                <div class="line">{{ $cashAdvance->requested_by }}</div>
            </td>
            <td>
                <div>Approved By,</div>
                <div class="line">{{ $cashAdvance->signers['approved_by'] ?? '........................' }}</div>
            </td>
            <td>
                <div>Verified By,</div>
                <div class="line">{{ $cashAdvance->signers['verified_by'] ?? '........................' }}</div>
            </td>
            <td>
                <div>Received By,</div>
                <div class="line">........................</div>
            </td>
        </tr>
    </table>

</body>
</html>