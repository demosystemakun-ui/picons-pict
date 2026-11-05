<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Advance - {{ $cashAdvance->number ?? $cashAdvance->id }}</title>
    <style>
        @page { margin: 20px 30px; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #000; }
        
        /* HEADER */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .header-table td { vertical-align: middle; }
        .logo { height: 45px; }
        .company-name { font-size: 18px; font-weight: bold; color: #1e3a8a; margin: 0; text-transform: uppercase; }
        .company-address { font-size: 9px; color: #555; margin: 2px 0 0 0; line-height: 1.2; }
        
        /* TITLE */
        .title-bar { background-color: #701a75; color: #fff; text-align: center; padding: 6px; font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .title-number { background-color: #701a75; color: #fff; text-align: center; padding: 4px; font-size: 11px; font-weight: bold; margin-bottom: 10px; }
        
        /* INFO SECTION */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; border: 1px solid #000; }
        .info-table td, .info-table th { border: 1px solid #000; padding: 4px 6px; }
        .info-table th { background-color: #f3f4f6; text-align: left; font-weight: bold; width: 15%; font-size: 9px; }
        .info-table td { width: 35%; font-size: 10px; }
        
        /* ITEMS TABLE */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .items-table th { background-color: #701a75; color: #fff; padding: 6px; border: 1px solid #000; font-weight: bold; font-size: 9px; text-align: center; }
        .items-table td { border: 1px solid #000; padding: 6px; font-size: 10px; }
        .items-table .text-center { text-align: center; }
        .items-table .text-right { text-align: right; }
        
        /* TOTALS & TRANSFER INFO */
        .bottom-section { width: 100%; margin-top: 15px; }
        .bottom-section td { vertical-align: top; }
        
        .transfer-table { width: 100%; border-collapse: collapse; border: 1px solid #000; }
        .transfer-table th { background-color: #701a75; color: #fff; text-align: left; padding: 4px 6px; font-weight: bold; font-size: 9px; width: 30%; }
        .transfer-table td { border: 1px solid #000; padding: 4px 6px; font-size: 10px; }
        
        .subtotal-table { width: 100%; border-collapse: collapse; border: 1px solid #000; }
        .subtotal-table th { background-color: #115e59; color: #fff; text-align: right; padding: 6px; font-weight: bold; font-size: 11px; }
        .subtotal-table td { text-align: right; padding: 6px; font-weight: bold; font-size: 11px; }

        /* SIGNATURES */
        .signature-table { width: 100%; margin-top: 30px; border-collapse: collapse; text-align: center; }
        .signature-table td { width: 20%; padding: 0 10px; }
        .sig-box { border: 1px solid #000; padding: 10px 5px; height: 90px; position: relative; }
        .sig-title { font-weight: bold; font-size: 9px; margin-bottom: 25px; }
        .sig-name { font-weight: bold; font-size: 10px; text-decoration: underline; }
        .sig-role { font-size: 9px; color: #333; margin-top: 2px; }
    </style>
</head>
<body>

    {{-- 1. HEADER --}}
    <table class="header-table">
        <tr>
            <td width="15%">
                {{-- Pastikan path logo sesuai dengan folder public Anda --}}
                <img src="{{ public_path('images/pict-logo.png') }}" class="logo" alt="PICT">
            </td>
            <td width="85%" style="text-align: right;">
                <h1 class="company-name">PT PATIMBAN INTERNATIONAL CAR TERMINAL</h1>
                <p class="company-address">
                    Address: Pelabuhan Patimban, Kec. Pusakanagara, Kab. Subang,<br>
                    Jawa Barat - Indonesia (41255)
                </p>
            </td>
        </tr>
    </table>

    {{-- 2. TITLE --}}
    <div class="title-bar">CASH ADVANCE FORM</div>
    <div class="title-number">{{ $cashAdvance->number ?? 'DRAFT' }}</div>

    {{-- 3. INFO KIRI & KANAN --}}
    <table class="info-table">
        <tr>
            <th>DATE</th>
            <td>: {{ \Carbon\Carbon::parse($cashAdvance->date)->format('d F Y') }}</td>
            <th>REQUEST DATE</th>
            <td>: {{ \Carbon\Carbon::parse($cashAdvance->request_date)->format('l, d F Y') }}</td>
        </tr>
        <tr>
            <th>CREATED BY</th>
            <td>: {{ $cashAdvance->created_by_name ?? 'System' }}</td>
            <th>REQUEST BY</th>
            <td>: {{ $cashAdvance->requested_by }}</td>
        </tr>
        <tr>
            <th>DEPARTMENT</th>
            <td>: {{ $cashAdvance->department }}</td>
            <th>DEPARTMENT</th>
            <td>: {{ $cashAdvance->department }}</td>
        </tr>
        <tr>
            <th>POSITION</th>
            <td>: {{ $cashAdvance->position ?? '-' }}</td>
            <th>Cost Department</th>
            <td>: {{ $cashAdvance->cost_department ?? '-' }}</td>
        </tr>
        <tr>
            <th>Attachment</th>
            <td>: {{ $cashAdvance->attachment ?? '-' }}</td>
            <th>Delivery Address</th>
            <td>: {{ $cashAdvance->delivery_address ?? '-' }}</td>
        </tr>
    </table>

    {{-- 4. TABEL BARANG --}}
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="35%">ITEM DESCRIPTION</th>
                <th width="25%">PURPOSE</th>
                <th width="8%">QTY</th>
                <th width="8%">UNIT</th>
                <th width="10%">PRICE</th>
                <th width="9%">TOTAL PRICE</th>
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
                    <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            {{-- Baris Kosong untuk Estetika (seperti di gambar) --}}
            @for($i = count($cashAdvance->items); $i < 6; $i++)
                <tr>
                    <td class="text-center">&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- 5. TRANSFER INFO & SUBTOTAL --}}
    <table class="bottom-section">
        <tr>
            {{-- Kiri: Info Transfer --}}
            <td width="55%" style="padding-right: 20px;">
                <table class="transfer-table">
                    <tr>
                        <th>Transfer To</th>
                        <td>: {{ $cashAdvance->account_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Bank Name</th>
                        <td>: {{ $cashAdvance->bank_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Bank Acc. No.</th>
                        <td>: {{ $cashAdvance->account_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Acc. Name</th>
                        <td>: {{ $cashAdvance->account_name ?? '-' }}</td>
                    </tr>
                </table>
            </td>

            {{-- Kanan: Sub Total --}}
            <td width="45%" style="vertical-align: bottom;">
                <table class="subtotal-table">
                    <tr>
                        <th width="40%">Sub Total</th>
                        <td>Rp {{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 6. KOLOM TANDA TANGAN --}}
    @php
        // Ambil data signer (jika disimpan dalam bentuk JSON di kolom 'signers')
        $signers = is_string($cashAdvance->signers) ? json_decode($cashAdvance->signers, true) : $cashAdvance->signers;
        $signers = $signers ?? [];
    @endphp
    <table class="signature-table">
        <tr>
            <td>
                <div class="sig-box">
                    <div class="sig-title">Request By</div>
                    <div class="sig-name">{{ $cashAdvance->requested_by }}</div>
                    <div class="sig-role">{{ $cashAdvance->position ?? 'Requester' }}</div>
                </div>
            </td>
            <td>
                <div class="sig-box">
                    <div class="sig-title">Checked By</div>
                    <div class="sig-name">{{ $signers['checked_by'] ?? '........................' }}</div>
                    <div class="sig-role">Operation Manager</div>
                </div>
            </td>
            <td>
                <div class="sig-box">
                    <div class="sig-title">Acknowledged By</div>
                    <div class="sig-name">{{ $signers['acknowledged_by'] ?? '........................' }}</div>
                    <div class="sig-role">COO</div>
                </div>
            </td>
            <td>
                <div class="sig-box">
                    <div class="sig-title">Verified By</div>
                    <div class="sig-name">{{ $signers['verified_by'] ?? '........................' }}</div>
                    <div class="sig-role">CFO</div>
                </div>
            </td>
            <td>
                <div class="sig-box">
                    <div class="sig-title">Approved By</div>
                    <div class="sig-name">{{ $signers['approved_by'] ?? '........................' }}</div>
                    <div class="sig-role">President Director</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>