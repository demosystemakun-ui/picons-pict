{{--
  Dokumen Claim Reimbursement Form (layout berbasis <table> agar aman untuk DomPDF & browser print)
  Variabel: $reimbursement, $logo (path/URL logo)
--}}
@php
    \Carbon\Carbon::setLocale('id');
    $rp        = fn ($n) => 'Rp' . number_format($n, 0, ',', '.');
    $rpDec     = fn ($n) => 'Rp' . number_format($n, 2, ',', '.');
    $d         = fn ($date) => $date ? $date->translatedFormat('j F Y') : '';
    // Penandatangan opsional: kosong = kotak tanpa nama
    $sg        = fn ($key, $field) => data_get($reimbursement->signers ?? [], "$key.$field") ?? '';
    
    $minRows   = 12;
    $items     = $reimbursement->items->values();
    $itemCount = $items->count();
    $blank     = max(0, $minRows - $itemCount);
    
    // Format Period menggunakan " Until "
    $period    = ($reimbursement->period_start ? $reimbursement->period_start->format('M j, Y') : '')
               . ($reimbursement->period_end ? ' Until ' . $reimbursement->period_end->format('M j, Y') : '');

    // Logika penggabungan sel DATE untuk baris data asli
    $dates = $items->map(fn ($it) => $it->date_display)->all();
    $spans = [];
    for ($i = 0, $n = count($dates); $i < $n; ) {
        $j = $i;
        while ($j + 1 < $n && $dates[$i] !== '' && $dates[$j + 1] === $dates[$i]) {
            $j++;
        }
        $spans[$i] = $j - $i + 1;
        for ($k = $i + 1; $k <= $j; $k++) {
            $spans[$k] = 0;
        }
        $i = $j + 1;
    }
@endphp

<style>
    .crf, .crf table { font-family: Helvetica, Arial, sans-serif; font-size: 9.5px; color: #000; }
    .crf table { border-collapse: collapse; width: 100%; }
    .crf td, .crf th { padding: 4px 5px; vertical-align: middle; }
    .crf .bd  td, .crf .bd th { border: 1px solid #000; }
    .crf .green { background-color: #92d050 !important; }
    
    /* Penebalan Teks (Bold) */
    .crf .b { font-weight: bold; }
    .crf .c { text-align: center; }
    .crf .r { text-align: right; }
    
    /* Styling KOP Surat */
    .crf .title { font-size: 17px; font-weight: bold; text-align: center; }
    
    .crf .company { 
        font-size: 20px; 
        font-weight: bold; 
        color: #1f3864; 
        text-align: left; /* Rata kiri */
        letter-spacing: 0.5px; 
        line-height: 1.1; 
        margin-bottom: 2px;
    }
    .crf .addr { 
        font-size: 10px; 
        font-weight: bold; 
        letter-spacing: 1.5px; 
        color: #1f3864; 
        text-align: left; /* Rata kiri */
        line-height: 1.4; 
    }
    
    /* Styling Khusus Tabel Item */
    .crf .items th { font-weight: bold; text-align: center; }
    .crf .items td { height: 18px; font-weight: bold; } 
    
    .crf .sign td { border: 1px solid #000; height: 105px; width: 19%; text-align: center; vertical-align: top; padding: 5px; }
    .crf .sign .gap { border: none; width: 1.25%; }
    .crf .sign .name { font-size: 9px; font-weight: bold; }

    /* Memastikan warna background hijau ikut tercetak di browser */
    @media print {
        .crf .green {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            background-color: #92d050 !important;
        }
    }
</style>

<div class="crf">

    {{-- KOP SURAT (Format Rapi Seperti Excel) --}}
    <table style="width: 100%; border: none; margin-bottom: 10px;">
        <tr>
            {{-- Kolom Logo --}}
            <td style="width: 20%; vertical-align: middle; text-align: left; padding-left: 0;">
                @if(!empty($logo)) 
                    <img src="{{ $logo }}" style="width: 170px; height: auto;"> 
                @endif
            </td>
            
            {{-- Kolom Teks --}}
            <td style="width: 80%; vertical-align: middle; text-align: left; padding-left: 10px;">
                <div class="company">PT PATIMBAN INTERNATIONAL CAR TERMINAL</div>
                <div class="addr">
                    Address : Pelabuhan Patimban, Kec. Pusakanagara, Kab.<br>
                    Subang,<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jawa Barat - Indonesia (41255)
                </div>
            </td>
        </tr>
    </table>

    {{-- JUDUL --}}
    <table class="bd" style="margin-top:6px;">
        <tr class="green">
            <td class="title" style="border-bottom:none;">CLAIM REIMBURSEMENT FORM</td>
        </tr>
        <tr class="green">
            <td class="c b" style="border-top:none;">No : &nbsp; {{ $reimbursement->number }}</td>
        </tr>
    </table>

    {{-- INFO --}}
    <table class="bd">
        <tr>
            <td class="b" style="width:14%;">REQUEST DATE</td><td class="b" style="width:2%;">:</td>
            <td class="b" style="width:34%;">{{ $d($reimbursement->request_date) }}</td>
            <td class="b" style="width:14%;">POSITION</td><td class="b" style="width:2%;">:</td>
            <td class="b">{{ $reimbursement->position }}</td>
        </tr>
        <tr>
            <td class="b">REQUESTED BY</td><td class="b">:</td>
            <td class="b">{{ $reimbursement->requested_by }}</td>
            <td class="b">DEPARTMENT</td><td class="b">:</td>
            <td class="b">{{ $reimbursement->department }}</td>
        </tr>
        <tr>
            <td class="b">NIK</td><td class="b">:</td>
            <td class="b">{{ $reimbursement->nik }}</td>
            <td class="b">PERIOD</td><td class="b">:</td>
            <td class="b">{{ $period }}</td>
        </tr>
    </table>

    {{-- TABEL ITEM + TOTAL --}}
    <table class="bd items" style="margin-top:6px;">
        {{-- Header Tabel --}}
        <tr class="green c b">
            <th rowspan="2" style="width:4%;">NO</th>
            <th colspan="2">ITEM DESCRIPTION</th>
            <th rowspan="2" style="width:10%;">RECEIPT No</th>
            <th rowspan="2" style="width:22%;">PURPOSE</th>
            <th rowspan="2" style="width:5%;">QTY</th>
            <th rowspan="2" style="width:6%;">UNIT</th>
            <th rowspan="2" style="width:12%;">PRICE</th>
            <th rowspan="2" style="width:15%;">TOTAL PRICE</th>
        </tr>
        <tr class="green c b">
            <th style="width:12%;">DATE</th>
            <th style="width:20%;">DETAILS</th>
        </tr>

        {{-- BARIS DATA ASLI (Dengan logika rowspan date) --}}
        @foreach($items as $i => $item)
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                
                @if(isset($spans[$i]) && $spans[$i] > 0)
                    <td class="c" rowspan="{{ $spans[$i] }}">{{ $item->date_display }}</td>
                @endif
                
                <td>{{ $item->description }}</td>
                <td class="c">{{ $item->receipt_no }}</td>
                <td class="c">{{ $item->purpose }}</td>
                <td class="c">{{ $item->qty }}</td>
                <td class="c">{{ $item->unit }}</td>
                <td class="r">{{ $rpDec($item->price) }}</td>
                <td class="r">{{ $rpDec($item->total) }}</td>
            </tr>
        @endforeach

        {{-- BARIS KOSONG (Tepat 9 kolom, TIDAK menggunakan rowspan) --}}
        @for($k = 0; $k < $blank; $k++)
            <tr>
                <td class="c">{{ $itemCount + $k + 1 }}</td>
                <td></td> {{-- DATE --}}
                <td></td> {{-- DETAILS --}}
                <td></td> {{-- RECEIPT --}}
                <td></td> {{-- PURPOSE --}}
                <td></td> {{-- QTY --}}
                <td></td> {{-- UNIT --}}
                <td></td> {{-- PRICE --}}
                <td></td> {{-- TOTAL PRICE --}}
            </tr>
        @endfor

        {{-- TOTAL (colspan 7 karena total kolom ada 9) --}}
        <tr>
            <td colspan="7" style="border:none; background:none;"></td>
            <td class="green c b">TOTAL</td>
            <td class="green r b">{{ $rp($reimbursement->total) }}</td>
        </tr>
    </table>

    {{-- NOTES + PAYMENT INFO --}}
    <table style="margin-top:12px;">
        <tr>
            <td style="width:58%; vertical-align:top; padding:0;">
                <table class="bd">
                    <tr><td class="c b" style="height:18px;">Notes</td></tr>
                    <tr><td class="b" style="height:34px; vertical-align:top;">{!! nl2br(e($reimbursement->notes)) !!}</td></tr>
                </table>
            </td>
            <td style="width:2%;"></td>
            <td style="vertical-align:top; padding:0;">
                <table class="bd">
                    <tr class="green"><td class="c b">Payment Info:</td></tr>
                    <tr>
                        <td class="b" style="height:34px; vertical-align:top;">
                            Bank Name : {{ $reimbursement->bank_name }}<br>
                            Acc Name : {{ $reimbursement->account_name }}<br>
                            Acc No. : {{ $reimbursement->account_number }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="sign" style="margin-top:10px;">
        <tr>
            @foreach(['requested' => 'REQUESTED BY', 'checked' => 'CHECKED', 'acknowledge' => 'ACKNOWLEDGE', 'verified' => 'VERIFIED', 'approved' => 'APPROVED'] as $key => $label)
                @php
                    $sName  = $sg($key, 'name');
                    $sTitle = $sg($key, 'title');
                    if ($key === 'requested') {
                        $sName  = $sName  ?: $reimbursement->requested_by;
                        $sTitle = $sTitle ?: $reimbursement->position;
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

    <table style="margin-top:8px; width:19%;">
        <tr><td class="b" style="border:1px solid #000; height:34px; vertical-align:top;">Legalized Form :</td></tr>
    </table>
</div>