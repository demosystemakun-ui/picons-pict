{{-- resources/views/procurement/pdf-final.blade.php --}}
@php
    $vendors     = $procurement['vendors'] ?? [];
    $winner      = $procurement['winner']  ?? null;
    $budgetType  = $procurement['budget_type'] ?? null;

    if (! function_exists('pr_pdf_check')) {
        function pr_pdf_check(bool $checked, int $size = 11): string {
            if ($checked) {
                return '<span style="display:inline-block;width:'.$size.'px;height:'.$size.'px;border:1.2px solid #000;background:#000;text-align:center;line-height:'.($size - 3).'px;color:#fff;font-size:'.($size - 2).'px;font-weight:bold;margin-right:4px;vertical-align:middle;">v</span>';
            }
            return '<span style="display:inline-block;width:'.$size.'px;height:'.$size.'px;border:1.2px solid #000;background:#fff;margin-right:4px;vertical-align:middle;"></span>';
        }
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Procurement Request Form — {{ $procurement['no'] }}</title>
<style>
    @page { margin: 0; }
    * { box-sizing: border-box; }
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        font-size: 9px;
        color: #000;
        margin: 25px 30px;
    }
    table { width: 100%; border-collapse: collapse; }
    .main-table { table-layout: fixed; border: 1.5px solid #000; }
    .main-table td, .main-table th {
        border: 1px solid #000;
        padding: 3px 5px;
        vertical-align: top;
    }
    .title-cell { text-align: center; padding: 6px !important; }
    .title {
        font-size: 16px; font-weight: bold;
        display: inline-block;
        border-bottom: 1.8px solid #000;
        padding-bottom: 2px;
    }
    .no-cell { text-align: center; font-size: 10px; padding: 4px !important; }
    .no-cell strong { font-family: 'Courier New', monospace; }
    .label-col { width: 22%; font-weight: bold; font-size: 8.5px; }
    .budget-col { width: 12%; text-align: center; font-size: 7.5px; }
    .section-header {
        background: #d9d9d9;
        font-weight: bold;
        text-align: center;
        font-size: 9.5px;
        padding: 4px !important;
    }
    .vendor-wrap { display: table; width: 100%; table-layout: fixed; }
    .vendor-cell {
        display: table-cell;
        width: 33.333%;
        padding: 6px;
        vertical-align: top;
        border-right: 1px solid #000;
    }
    .vendor-cell:last-child { border-right: none; }
    .vendor-card {
        border: 1.2px solid #000;
        padding: 6px 4px;
        text-align: center;
    }
    .vendor-card .header {
        font-size: 8px;
        padding-bottom: 3px;
        border-bottom: 1px solid #000;
        margin-bottom: 4px;
    }
    .vendor-card .body {
        font-size: 8.5px;
        min-height: 24px;
        line-height: 1.3;
    }
    .vendor-card .total-lbl {
        font-size: 8px;
        margin-top: 5px;
        padding-top: 4px;
        border-top: 1px solid #000;
    }
    .vendor-card .total-val { font-weight: bold; font-size: 9px; margin-top: 1px; }
    .vendor-card .tax { font-size: 7.5px; margin-top: 1px; }
    .notes {
        font-size: 7.5px;
        padding: 4px 6px !important;
        line-height: 1.4;
    }
    .decision-table { width: 100%; table-layout: fixed; }
    .decision-table td { border: 1px solid #000; padding: 4px; text-align: center; }
    .sign-box { border: 1px solid #000; height: 36px; margin-bottom: 3px; }
    .sign-lbl { text-align: center; font-size: 8px; font-weight: bold; padding-top: 2px; }
</style>
</head>
<body>

<table class="main-table">

    {{-- ═══ HEADER ═══ --}}
    <tr>
        <td colspan="5" class="title-cell">
            <span class="title">Procurement Request form</span>
        </td>
    </tr>
    <tr>
        <td colspan="5" class="no-cell">
            No. &nbsp; <strong>{{ $procurement['no'] }}</strong>
        </td>
    </tr>

    {{-- ═══ BASIC INFO ═══ --}}
    <tr>
        <td class="label-col" style="width:18%;">DATE</td>
        <td style="width:32%;">{{ $procurement['date'] }}</td>
        <td class="label-col" style="width:12%; text-align:center;">Budget issue</td>
        <td class="budget-col">{!! pr_pdf_check($budgetType === 'budgeted') !!} Budgeted</td>
        <td class="budget-col">{!! pr_pdf_check($budgetType === 'non_budgeted') !!} Non Budgeted</td>
    </tr>
    <tr>
        <td class="label-col">REQUEST BY</td>
        <td colspan="4">{{ $procurement['request_by'] }}</td>
    </tr>
    <tr>
        <td class="label-col">DIVISION</td>
        <td colspan="4">{{ $procurement['division'] }}</td>
    </tr>
    <tr>
        <td class="label-col">PREPARED BY</td>
        <td colspan="4">{{ $procurement['prepared_by'] }}</td>
    </tr>
    <tr>
        <td class="label-col">DESCRIPTION</td>
        <td colspan="4">{{ $procurement['description'] }}</td>
    </tr>

    {{-- ═══ CANDIDATES OF SUPPLIERS ═══ --}}
    <tr>
        <td colspan="5" class="section-header">Candidates of suppliers</td>
    </tr>
    <tr>
        <td colspan="5" style="padding: 6px; border: 1px solid #000;">
            @if(count($vendors) > 0)
                <div class="vendor-wrap">
                    @foreach(array_slice($vendors, 0, 3) as $v)
                        <div class="vendor-cell">
                            <div class="vendor-card">
                                <div class="header">Company name</div>
                                <div class="body">
                                    <strong>{{ $v['vendor_name'] }}</strong>
                                    @if(!empty($v['marketplace']))
                                        <br><span style="font-size:7px; color:#555;">({{ $v['marketplace'] }})</span>
                                    @endif
                                </div>
                                <div class="total-lbl">TOTAL price</div>
                                <div class="total-val">
                                    Total: {{ number_format($v['total_price'], 0, ',', '.') }}
                                </div>
                                <div class="tax">{{ $v['tax_note'] ?? '' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align:center; font-size:8.5px; color:#666; padding: 12px 0;">
                    Belum ada vendor yang di-input untuk PR ini.
                </div>
            @endif
        </td>
    </tr>

    {{-- ═══ NOTES ═══ --}}
    <tr>
        <td colspan="5" class="notes">
            ※ The contents of quotation should be attached (at least 3 or more)<br>
            ※ If the supplier recommend similar products instead of our request, please indicate the reason
        </td>
    </tr>

    {{-- ═══ DECISION ON COMPARISON ═══ --}}
    <tr>
        <td colspan="3" class="section-header">Decision by Operations group</td>
        <td colspan="2" class="section-header">Approved by</td>
    </tr>
    <tr>
        <td colspan="3" style="padding: 0; border: 1px solid #000;">
            <table class="decision-table">
                <tr>
                    <td style="width: 55%; font-size: 8.5px;">Vendor/Supplier name</td>
                    <td style="width: 45%; font-size: 8.5px;">Total Price</td>
                </tr>
                <tr>
                    <td style="padding: 14px 4px; font-weight: bold; font-size: 9px;">
                        {{ $winner['vendor_name'] ?? '—' }}
                    </td>
                    <td style="padding: 14px 4px;">
                        @if($winner)
                            <div style="font-weight:bold; font-size:9px;">
                                Total: {{ number_format($winner['total_price'], 0, ',', '.') }}
                            </div>
                            <div style="font-size:7.5px;">{{ $winner['tax_note'] ?? '' }}</div>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            </table>
        </td>

        <td colspan="2" rowspan="3" style="padding: 6px; vertical-align: top;">
            <div class="sign-box"></div>
            <div class="sign-lbl">Ops. MGR</div>
            <div class="sign-box" style="margin-top: 10px;"></div>
            <div class="sign-lbl">FEM. MGR</div>
            <div class="sign-box" style="margin-top: 10px;"></div>
            <div class="sign-lbl">COO</div>
        </td>
    </tr>
    <tr>
        <td colspan="3" class="section-header">Reason why choose this vendor/supplier</td>
    </tr>
    <tr>
        <td colspan="3" style="padding: 10px 6px; min-height: 60px; font-size: 8.5px; line-height: 1.4;">
            @if(!empty($procurement['reason_choose_vendor']))
                {{ $procurement['reason_choose_vendor'] }}
            @elseif($winner)
                {{ $winner['vendor_name'] }} menawarkan harga total terendah
                (Rp {{ number_format($winner['total_price'], 0, ',', '.') }})
                dibandingkan vendor lain, sehingga dipilih sebagai supplier untuk pengadaan ini.
            @else
                —
            @endif
        </td>
    </tr>

    {{-- ═══ FINAL DECISION ═══ --}}
    <tr>
        <td colspan="5" class="section-header">Final decision</td>
    </tr>
    <tr>
        <td colspan="5" style="padding: 0;">
            <table class="decision-table">
                <tr>
                    <td style="width: 55%; font-size: 8.5px;">Vendor/Supplier name</td>
                    <td style="width: 45%; font-size: 8.5px;">Total Price</td>
                </tr>
                <tr>
                    <td style="padding: 14px 4px; font-weight: bold; font-size: 9px;">
                        {{ $winner['vendor_name'] ?? '—' }}
                    </td>
                    <td style="padding: 14px 4px;">
                        @if($winner)
                            <div style="font-weight:bold; font-size:9px;">
                                Total: {{ number_format($winner['total_price'], 0, ',', '.') }}
                            </div>
                            <div style="font-size:7.5px;">{{ $winner['tax_note'] ?? '' }}</div>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- ═══ BOTTOM SIGNATURES ═══ --}}
    <tr>
        <td colspan="5" style="padding: 14px 8px;">
            <table style="width: 100%; table-layout: fixed;">
                <tr>
                    @foreach([
                        ['label' => 'Accounting MGR', 'name' => $procurement['accounting_mgr_name'] ?? ''],
                        ['label' => 'CFO',            'name' => $procurement['cfo_name'] ?? ''],
                        ['label' => 'CEO',            'name' => $procurement['ceo_name'] ?? ''],
                    ] as $sign)
                        <td style="width: 30%; padding: 0 6px; vertical-align: bottom;">
                            <div style="height: 34px; text-align: center; font-size: 8px; padding-top: 4px;">
                                {{ $sign['name'] }}
                            </div>
                            <div style="border: 1px solid #000; text-align: center; padding: 5px 0; font-size: 8.5px; font-weight: bold;">
                                {{ $sign['label'] }}
                            </div>
                        </td>
                    @endforeach
                    <td style="width: 10%;"></td>
                </tr>
            </table>
        </td>
    </tr>

</table>

</body>
</html>