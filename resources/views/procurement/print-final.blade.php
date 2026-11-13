{{-- resources/views/procurement/print-final.blade.php --}}
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
<title>Procurement Request Form (Final) — {{ $procurement['no'] }}</title>
<style>
    @page { size: a4 portrait; margin: 10mm 12mm; }
    * { box-sizing: border-box; }
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        font-size: 11px;
        color: #000;
        margin: 0;
        padding: 0;
    }

    /* ═══ CSS WATERMARK DRAFT ═══ */
    .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 150px;
        font-weight: 900;
        color: rgba(200, 200, 200, 0.35);
        z-index: 9999;
        text-align: center;
        white-space: nowrap;
        pointer-events: none;
        font-family: sans-serif;
        letter-spacing: 5px;
    }
    /* ═══════════════════════════ */

    table { width: 100%; border-collapse: collapse; }
    .main-table { table-layout: fixed; border: 1.5px solid #000; }
    .main-table td, .main-table th {
        border: 1px solid #000;
        padding: 4px 6px;
        vertical-align: top;
    }
    .title-cell { text-align: center; padding: 8px !important; }
    .title {
        font-size: 20px; font-weight: bold;
        display: inline-block;
        border-bottom: 2px solid #000;
        padding-bottom: 2px;
    }
    .no-cell { text-align: center; font-size: 12px; padding: 6px !important; }
    .no-cell strong { font-family: 'Courier New', monospace; font-size: 13px; }
    .label-col { width: 22%; font-weight: bold; font-size: 10px; }
    .budget-col { width: 12%; text-align: center; font-size: 9px; }
    .section-header {
        background: #d9d9d9;
        font-weight: bold;
        text-align: center;
        font-size: 11px;
        padding: 6px !important;
    }
    .vendor-wrap { display: table; width: 100%; table-layout: fixed; }
    .vendor-cell {
        display: table-cell;
        width: 33.333%;
        padding: 8px;
        vertical-align: top;
        border-right: 1px solid #000;
    }
    .vendor-cell:last-child { border-right: none; }
    .vendor-card {
        border: 1.2px solid #000;
        padding: 8px 6px;
        text-align: center;
    }
    .vendor-card .header {
        font-size: 10px;
        padding-bottom: 4px;
        border-bottom: 1px solid #000;
        margin-bottom: 6px;
    }
    .vendor-card .body {
        font-size: 10px;
        min-height: 30px;
        line-height: 1.3;
    }
    .vendor-card .total-lbl {
        font-size: 10px;
        margin-top: 8px;
        padding-top: 6px;
        border-top: 1px solid #000;
    }
    .vendor-card .total-val { font-weight: bold; font-size: 11px; margin-top: 2px; }
    .vendor-card .tax { font-size: 9px; margin-top: 2px; }
    .notes {
        font-size: 9px;
        padding: 6px 8px !important;
        line-height: 1.5;
    }
    .decision-table { width: 100%; table-layout: fixed; }
    .decision-table td { border: 1px solid #000; padding: 6px; text-align: center; }
    .sign-box { border: 1px solid #000; height: 45px; margin-bottom: 4px; position: relative; }
    .sign-lbl { text-align: center; font-size: 10px; font-weight: bold; padding-top: 2px; }
    .approved-stamp { font-size: 8px; color: #15803d; font-weight: bold; letter-spacing: 0.5px; margin-top: 4px; }
</style>
</head>
<body>

{{-- ═══ WATERMARK DRAFT ═══ --}}
@if(isset($isDraft) && $isDraft)
    <div class="watermark">DRAFT</div>
@endif
{{-- ═══════════════════════ --}}

<table class="main-table">

    {{-- ═══ HEADER ═══ --}}
    <tr>
        <td colspan="5" class="title-cell">
            <span class="title">Procurement Request Form</span>
        </td>
    </tr>
    <tr>
        <td colspan="5" class="no-cell">
            No. &nbsp;&nbsp;&nbsp; <strong>{{ $procurement['no'] }}</strong>
        </td>
    </tr>

    {{-- ═══ BASIC INFO ═══ --}}
    <tr>
        <td class="label-col" style="width:18%;">DATE</td>
        <td style="width:32%;">{{ $procurement['date'] }}</td>
        <td class="label-col" style="width:12%; text-align:center;">Budget issue</td>
        <td class="budget-col">{!! pr_pdf_check($budgetType === 'Budgeted') !!} Budgeted</td>
        <td class="budget-col">{!! pr_pdf_check($budgetType === 'Non Budgeted') !!} Non Budgeted</td>
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
        <td colspan="5" style="padding: 8px; border: 1px solid #000;">
            @if(count($vendors) > 0)
                <div class="vendor-wrap">
                    @foreach(array_slice($vendors, 0, 3) as $v)
                        <div class="vendor-cell">
                            <div class="vendor-card">
                                <div class="header">Company name</div>
                                <div class="body">
                                    <strong>{{ $v['company_name'] ?: $v['vendor_name'] }}</strong>
                                    @if(!empty($v['marketplace']))
                                        <br><span style="font-size:9px; color:#555;">({{ $v['marketplace'] }})</span>
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
                <div style="text-align:center; font-size:10px; color:#666; padding: 15px 0;">
                    No vendors have been added for this PR yet.
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
                    <td style="width: 55%; font-size: 10px;">Vendor/Supplier name</td>
                    <td style="width: 45%; font-size: 10px;">Total Price</td>
                </tr>
                <tr>
                    <td style="padding: 18px 4px; font-weight: bold; font-size: 11px;">
                        {{ $winner['vendor_name'] ?? '—' }}
                    </td>
                    <td style="padding: 18px 4px;">
                        @if($winner)
                            <div style="font-weight:bold; font-size:11px;">
                                Total: {{ number_format($winner['total_price'], 0, ',', '.') }}
                            </div>
                            <div style="font-size:9px;">{{ $winner['tax_note'] ?? '' }}</div>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            </table>
        </td>

        {{-- AREA TANDA TANGAN KANAN --}}
        <td colspan="2" rowspan="3" style="padding: 8px; vertical-align: top;">
            @foreach($procurement['signatures'] as $sig)
                <div class="sign-box" style="position: relative;">
                    <div style="text-align: center; font-size: 9px; padding-top: 4px;">
                        {{ $sig['name'] }}
                    </div>
                    @if($sig['approved'])
                        <div style="position: absolute; bottom: 4px; left: 0; right: 0; text-align: center; font-size: 8px; color: #15803d; font-weight: bold;">
                            [ APPROVED ]
                        </div>
                    @endif
                </div>
                <div class="sign-lbl">{{ $sig['role'] }}</div>
                @if(!$loop->last)
                    <div style="margin-top: 10px;"></div>
                @endif
            @endforeach
        </td>
    </tr>
    <tr>
        <td colspan="3" class="section-header">Reason why choose this vendor/supplier</td>
    </tr>
    <tr>
        <td colspan="3" style="padding: 12px 8px; min-height: 80px; font-size: 10px; line-height: 1.5;">
            @if(!empty($procurement['reason_choose_vendor']))
                {{ $procurement['reason_choose_vendor'] }}
            @elseif($winner)
                {{-- TEKS FALLBACK BAHASA INGGRIS --}}
                {{ $winner['vendor_name'] }} was selected because it offers the best value for money and meets all the required specifications for this procurement.
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
                    <td style="width: 55%; font-size: 10px;">Vendor/Supplier name</td>
                    <td style="width: 45%; font-size: 10px;">Total Price</td>
                </tr>
                <tr>
                    <td style="padding: 18px 4px; font-weight: bold; font-size: 11px;">
                        {{ $winner['vendor_name'] ?? '—' }}
                    </td>
                    <td style="padding: 18px 4px;">
                        @if($winner)
                            <div style="font-weight:bold; font-size:11px;">
                                Total: {{ number_format($winner['total_price'], 0, ',', '.') }}
                            </div>
                            <div style="font-size:9px;">{{ $winner['tax_note'] ?? '' }}</div>
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
        <td colspan="5" style="padding: 16px 10px;">
            <table style="width: 100%; table-layout: fixed;">
                <tr>
                    @foreach($procurement['bottom_signatures'] as $sign)
                        <td style="width: 30%; padding: 0 8px; vertical-align: bottom;">
                            <div style="height: 40px; position: relative; text-align: center;">
                                <div style="font-size: 10px; padding-top: 4px;">
                                    {{ $sign['name'] }}
                                </div>
                                @if($sign['approved'])
                                    <div style="position: absolute; bottom: 0; left: 0; right: 0; font-size: 8px; color: #15803d; font-weight: bold;">
                                        [ APPROVED ]
                                    </div>
                                @endif
                            </div>
                            <div style="border: 1px solid #000; text-align: center; padding: 6px 0; font-size: 10px; font-weight: bold;">
                                {{ $sign['role'] }}
                            </div>
                        </td>
                    @endforeach
                    <td style="width: 10%;"></td>
                </tr>
            </table>
        </td>
    </tr>

</table>

{{-- AUTO PRINT --}}
<script>
    (function () {
        const returnUrl = document.referrer || null;

        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 300);
        });

        window.addEventListener('afterprint', function () {
            setTimeout(function () {
                if (returnUrl) {
                    window.location.href = returnUrl;
                } else if (window.history.length > 1) {
                    window.history.back();
                }
            }, 100);
        });
    })();
</script>

</body>
</html>