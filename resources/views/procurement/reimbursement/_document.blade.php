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
    $period    = ($reimbursement->period_start ? $reimbursement->period_start->format('M j, Y') : '')
               . ($reimbursement->period_end ? ' - ' . $reimbursement->period_end->format('M j, Y') : '');

    // Gabungkan sel DATE untuk baris berurutan yang tanggalnya sama (rowspan)
    $dates = $items->map(fn ($it) => $it->date_display)->all();
    $spans = [];
    for ($i = 0, $n = count($dates); $i < $n; ) {
        $j = $i;
        while ($j + 1 < $n && $dates[$i] !== '' && $dates[$j + 1] === $dates[$i]) {
            $j++;
        }
        $spans[$i] = $j - $i + 1;              // baris pertama grup: rowspan
        for ($k = $i + 1; $k <= $j; $k++) {
            $spans[$k] = 0;                    // baris berikutnya: sel DATE dilewati
        }
        $i = $j + 1;
    }
@endphp

<style>
    .crf, .crf table { font-family: Helvetica, Arial, sans-serif; font-size: 9.5px; color: #000; }
    .crf table { border-collapse: collapse; width: 100%; }
    .crf td, .crf th { padding: 2px 4px; vertical-align: middle; }
    .crf .bd  td, .crf .bd th { border: 1px solid #000; }
    .crf .green { background-color: #92d050 !important; }
    .crf .b { font-weight: bold; }
    .crf .c { text-align: center; }
    .crf .r { text-align: right; }
    .crf .title { font-size: 17px; font-weight: bold; text-align: center; }
    .crf .company { font-size: 19px; font-weight: bold; color: #1f3864; text-align: center; letter-spacing: .5px; line-height: 1.15; }
    .crf .addr { font-size: 10px; font-weight: bold; letter-spacing: 1.6px; color: #1f3864; text-align: center; line-height: 1.5; margin-top: 4px; }
    .crf .items td { height: 15px; }
    .crf .sign td { border: 1px solid #000; height: 105px; width: 19%; text-align: center; vertical-align: top; padding: 3px; }
    .crf .sign .gap { border: none; width: 1.25%; }
    .crf .sign .name { font-size: 9px; }

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

    {{-- KOP --}}
    <table>
        <tr>
            <td style="width:22%; vertical-align:middle;">
                @if(!empty($logo)) <img src="{{ $logo }}" style="width:150px; height:auto;"> @endif
            </td>
            <td style="vertical-align:middle; text-align:center;">
                <div class="company">PT PATIMBAN INTERNATIONAL CAR TERMINAL</div>
                <div class="addr">
                    Address : Pelabuhan Patimban, Kec. Pusakanagara, Kab. Subang,<br>
                    Jawa Barat - Indonesia (41255)
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
            <td class="b" style="width:14%;">REQUEST DATE</td><td style="width:2%;">:</td>
            <td style="width:34%;">{{ $d($reimbursement->request_date) }}</td>
            <td class="b" style="width:14%;">POSITION</td><td style="width:2%;">:</td>
            <td>{{ $reimbursement->position }}</td>
        </tr>
        <tr>
            <td class="b">REQUESTED BY</td><td>:</td>
            <td class="b">{{ $reimbursement->requested_by }}</td>
            <td class="b">DEPARTMENT</td><td>:</td>
            <td>{{ $reimbursement->department }}</td>
        </tr>
        <tr>
            <td class="b">NIK</td><td>:</td>
            <td>{{ $reimbursement->nik }}</td>
            <td class="b">PERIOD</td><td>:</td>
            <td>{{ $period }}</td>
        </tr>
    </table>

    {{-- TABEL ITEM + TOTAL --}}
    <table class="bd items" style="margin-top:6px;">
        <tr class="green c b">
            <th style="width:3%;">NO</th>
            <th style="width:15%;">DATE</th>
            <th style="width:18%;">DETAILS</th>
            <th style="width:9%;">RECEIPT No</th>
            <th style="width:20%;">PURPOSE</th>
            <th style="width:4%;">QTY</th>
            <th style="width:5%;">UNIT</th>
            <th style="width:11%;">PRICE</th>
            <th style="width:15%;">TOTAL PRICE</th>
        </tr>

        @foreach($items as $i => $item)
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                @if($spans[$i] > 0)
                    <td class="c" rowspan="{{ $spans[$i] }}">{{ $item->date_display }}</td>
                @endif
                <td>{{ $item->description }}</td>
                <td>{{ $item->receipt_no }}</td>
                <td class="c">{{ $item->purpose }}</td>
                <td class="c">{{ $item->qty }}</td>
                <td class="c">{{ $item->unit }}</td>
                <td class="r">{{ $rpDec($item->price) }}</td>
                <td class="r">{{ $rpDec($item->total) }}</td>
            </tr>
        @endforeach

        {{-- Baris kosong --}}
        @for($k = 0; $k < $blank; $k++)
            <tr>
                <td class="c">{{ $itemCount + $k + 1 }}</td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
        @endfor

        {{-- TOTAL --}}
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
                    <tr><td style="height:34px; vertical-align:top;">{!! nl2br(e($reimbursement->notes)) !!}</td></tr>
                </table>
            </td>
            <td style="width:2%;"></td>
            <td style="vertical-align:top; padding:0;">
                <table class="bd">
                    <tr class="green"><td class="c b">Payment Info:</td></tr>
                    <tr>
                        <td style="height:34px; vertical-align:top;">
                            Bank Name : {{ $reimbursement->bank_name }}<br>
                            Acc Name : <b>{{ $reimbursement->account_name }}</b><br>
                            Acc No. : {{ $reimbursement->account_number }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- TANDA TANGAN (opsional: kosong = kotak tanpa nama) --}}
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
                    <div>{{ $label }}</div>
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
        <tr><td style="border:1px solid #000; height:34px; vertical-align:top;">Legalized Form :</td></tr>
    </table>
</div>