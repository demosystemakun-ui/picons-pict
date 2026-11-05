@php
    \Carbon\Carbon::setLocale('en');
    $rp    = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    // Format tanggal: Monday, 13 April 2026
    $d     = fn ($date) => $date ? $date->translatedFormat('l, j F Y') : '';
    $sg    = fn ($key, $field) => data_get($cashAdvance->signers ?? [], "$key.$field") ?? '';

    $minRows   = 10;
    $items     = $cashAdvance->items->values();
    $itemCount = $items->count();
    $blank     = max(0, $minRows - $itemCount);
@endphp

<style>
    .ca { font-family: Helvetica, Arial, sans-serif; font-size: 9.5px; color: #000; }
    .ca table { border-collapse: collapse; width: 100%; }
    .ca td, .ca th { padding: 4px 5px; vertical-align: middle; }
    .ca .bd td, .ca .bd th { border: 1px solid #000; }
    .ca .purple { background-color: #8B3A8E !important; color: #fff; }
    .ca .b { font-weight: bold; }
    .ca .c { text-align: center; }
    .ca .r { text-align: right; }
    .ca .title { font-size: 16px; font-weight: bold; text-align: center; }
    .ca .company { font-size: 20px; font-weight: bold; color: #1f3864; }
    .ca .addr { font-size: 10px; font-weight: bold; color: #1f3864; }
    .ca .items th { font-weight: bold; text-align: center; }
    .ca .items td { height: 18px; }
    .ca .sign td { border: 1px solid #000; height: 105px; width: 19%; text-align: center; vertical-align: top; padding: 5px; }
    .ca .sign .gap { border: none; width: 1.25%; }
    .ca .sign .name { font-size: 9px; font-weight: bold; }
    @media print {
        .ca .purple { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>

<div class="ca">
    {{-- KOP SURAT --}}
    <table style="width: 100%; border: none; margin-bottom: 10px;">
        <tr>
            <td style="width: 20%; text-align: left;">
                @if(!empty($logo) && file_exists($logo))
                    <img src="{{ $logo }}" style="width: 170px; height: auto;">
                @endif
            </td>
            <td style="width: 80%; text-align: left; padding-left: 10px;">
                <div class="company">PT PATIMBAN INTERNATIONAL CAR TERMINAL</div>
                <div class="addr">
                    Address : Pelabuhan Patimban, Kec. Pusakanagara, Kab. Subang,<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jawa Barat - Indonesia (41255)
                </div>
            </td>
        </tr>
    </table>

    {{-- TITLE & NOMOR SURAT --}}
    <table class="bd" style="margin-top:6px;">
        <tr class="purple">
            <td class="title" style="border-bottom:none;">CASH ADVANCE FORM</td>
        </tr>
        <tr class="purple">
            <td class="c b" style="border-top:none;">
                @if($cashAdvance->status === 'approved' && !str_starts_with($cashAdvance->number, 'DRAFT'))
                    {{ $cashAdvance->number }}
                @else
                    <span class="italic opacity-80">Waiting for Admin Approval</span>
                @endif
            </td>
        </tr>
    </table>

    {{-- INFO --}}
    <table class="bd">
        <tr>
            <td class="b" style="width:14%;">DATE</td><td class="b" style="width:2%;">:</td>
            <td class="b" style="width:34%;">{{ $d($cashAdvance->request_date) }}</td>
            <td class="b" style="width:14%;">REQUEST DATE</td><td class="b" style="width:2%;">:</td>
            <td class="b">{{ $d($cashAdvance->request_date) }}</td>
        </tr>
        <tr>
            <td class="b">CREATED BY</td><td class="b">:</td>
            <td class="b">{{ $cashAdvance->requested_by }}</td>
            <td class="b">REQUEST BY</td><td class="b">:</td>
            <td class="b">{{ $cashAdvance->requested_by }}</td>
        </tr>
        <tr>
            <td class="b">DEPARTMENT</td><td class="b">:</td>
            <td class="b">{{ $cashAdvance->department }}</td>
            <td class="b">DEPARTMENT</td><td class="b">:</td>
            <td class="b">{{ $cashAdvance->cost_department }}</td>
        </tr>
        <tr>
            <td class="b">POSITION</td><td class="b">:</td>
            <td class="b">{{ $cashAdvance->position }}</td>
            <td class="b">Cost Department</td><td class="b">:</td>
            <td class="b">{{ $cashAdvance->cost_department }}</td>
        </tr>
        <tr>
            <td class="b">Attachment</td><td class="b">:</td>
            <td class="b">{{ $cashAdvance->attachment ?? '-' }}</td>
            <td class="b">Delivery Address</td><td class="b">:</td>
            <td class="b">{{ $cashAdvance->delivery_address ?? '-' }}</td>
        </tr>
    </table>

    {{-- TABLE ITEMS --}}
    <table class="bd items" style="margin-top:6px;">
        <thead>
            <tr class="purple c b">
                <th style="width:4%;">NO</th>
                <th style="width:35%;">ITEM DESCRIPTION</th>
                <th style="width:25%;">PURPOSE</th>
                <th style="width:6%;">QTY</th>
                <th style="width:8%;">UNIT</th>
                <th style="width:12%;">PRICE</th>
                <th style="width:12%;">TOTAL PRICE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ $item->item_description }}</td>
                    <td>{{ $item->purpose }}</td>
                    <td class="c">{{ $item->qty }}</td>
                    <td class="c">{{ $item->unit }}</td>
                    <td class="r">{{ $rp($item->price) }}</td>
                    <td class="r">{{ $rp($item->qty * $item->price) }}</td>
                </tr>
            @endforeach

            @for($k = 0; $k < $blank; $k++)
                <tr>
                    <td class="c">{{ $itemCount + $k + 1 }}</td>
                    <td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endfor

            <tr>
                <td colspan="5" style="border:none;"></td>
                <td class="purple c b">Sub Total</td>
                <td class="purple r b">{{ $rp($cashAdvance->total) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- PAYMENT INFO --}}
    <table style="margin-top:12px; width:40%;">
        <tr class="purple">
            <td class="c b" style="padding:4px;">Transfer To</td>
        </tr>
        <tr><td class="b" style="border:1px solid #000;">Bank Name : {{ $cashAdvance->bank_name }}</td></tr>
        <tr><td class="b" style="border:1px solid #000;">Bank Acc. No. : {{ $cashAdvance->account_number }}</td></tr>
        <tr><td class="b" style="border:1px solid #000;">Acc. Name : {{ $cashAdvance->account_name }}</td></tr>
    </table>

    {{-- SIGNATURES --}}
    <table class="sign" style="margin-top:20px;">
        <tr>
            @foreach(['requested' => 'Request By', 'checked' => 'Checked By', 'acknowledge' => 'Acknowledged By', 'verified' => 'Verified By', 'approved' => 'Approved By'] as $key => $label)
                @php
                    $sName  = $sg($key, 'name');
                    $sTitle = $sg($key, 'title');
                    if ($key === 'requested') {
                        $sName  = $sName  ?: $cashAdvance->requested_by;
                        $sTitle = $sTitle ?: $cashAdvance->position;
                    }
                @endphp
                <td>
                    <div class="b">{{ $label }}</div>
                    <div style="height:62px;"></div>
                    <div class="name">
                        {{ $sName }}@if($sName !== '' && $sTitle !== '')<br>@endif{{ $sTitle }}
                    </div>
                </td>
                @if(!$loop->last) <td class="gap"></td> @endif
            @endforeach
        </tr>
    </table>
</div>