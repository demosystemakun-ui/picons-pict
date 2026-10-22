{{-- resources/views/procurement/pdf.blade.php --}}
@php
    if (! function_exists('pr_pdf_nl2p')) {
        function pr_pdf_nl2p(?string $str): string {
            $cleaned = preg_replace("/[\r\n]{3,}/", "\n\n", trim($str ?? ''));
            return nl2br(e($cleaned));
        }
    }

    if (! function_exists('pr_pdf_checkmark')) {
        function pr_pdf_checkmark(bool $checked): string {
            $size = 20;
            $img = imagecreatetruecolor($size, $size);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);

            imagefill($img, 0, 0, $white);
            imagesetthickness($img, 2);
            imagerectangle($img, 1, 1, $size - 2, $size - 2, $black);

            if ($checked) {
                imagesetthickness($img, 3);
                imageline($img, 5, 10, 8, 14, $black);
                imageline($img, 8, 14, 15, 6, $black);
            }

            ob_start();
            imagepng($img);
            $pngData = ob_get_clean();
            imagedestroy($img);

            return '<img src="data:image/png;base64,' . base64_encode($pngData) . '" width="11" height="11" style="vertical-align:middle;margin-right:5px;" alt="">';
        }
    }

    $orderType = $procurement['order_type'] ?? [
        'new_order'    => false,
        'repeat_order' => false,
        'goods'        => false,
        'services'     => false,
    ];

    $items     = $procurement['purchase_items'] ?? [];
    $itemCount = max(count($items), 1);

    $signatures = $procurement['signatures'] ?? [
        ['role' => 'PIC',         'name' => ''],
        ['role' => 'Ops Manager', 'name' => ''],
        ['role' => 'FEM Manager', 'name' => ''],
        ['role' => 'COO',         'name' => ''],
    ];

    $orderTypeCells = [
        ['key' => 'new_order',    'label' => 'New Order',    'width' => '19%'],
        ['key' => 'repeat_order', 'label' => 'Repeat Order', 'width' => '20%'],
        ['key' => 'goods',        'label' => 'Goods',        'width' => '17%'],
        ['key' => 'services',     'label' => 'Services',     'width' => '20%'],
    ];

    $basicInfo = [
        ['label' => 'DATE',         'value' => $procurement['date']        ?? ''],
        ['label' => 'REQUEST BY',   'value' => $procurement['request_by']  ?? ''],
        ['label' => 'DIVISION',     'value' => $procurement['division']    ?? ''],
        ['label' => 'PREPARED BY',  'value' => $procurement['prepared_by'] ?? ''],
        ['label' => 'DESCRIPTIONS', 'value' => $procurement['description'] ?? ''],
    ];

    $textSections = [
        [
            'label' => 'Background',
            'hint'  => '(Why you would like to purchase?)',
            'value' => $procurement['background'] ?? '',
        ],
        [
            'label' => 'Purpose',
            'hint'  => '(What is the effect to purchase it? What is if not purchase it?)',
            'value' => $procurement['purpose'] ?? '',
        ],
        [
            'label' => 'Required spec',
            'hint'  => '(Detail and reason of it)',
            'value' => $procurement['required_spec'] ?? '',
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Procurement Request Form — {{ $procurement['no'] ?? '' }}</title>
<style>
    @page { margin: 0; }
    * { box-sizing: border-box; -webkit-box-sizing: border-box; }

    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        font-size: 9.5px;
        color: #000;
        margin: 35px 45px 25px 45px;
        padding: 0;
        background: #fff;
    }

    .sheet {
        width: 100%;
        background: #fff;
        margin: 0 auto;
        padding: 0;
    }

    table { width: 100%; border-collapse: collapse; }

    .main-table {
        table-layout: fixed;
        border: 1.5px solid #000;
    }
    .main-table td,
    .main-table th {
        border: 1px solid #000;
        padding: 3.5px 6px;
        vertical-align: middle;
        word-wrap: break-word;
    }

    .title-cell {
        text-align: center;
        padding: 5px 0 3px 0 !important;
        border: 1px solid #000 !important;
    }
    .bold-underline-title {
        font-size: 15px;
        font-weight: bold;
        display: inline-block;
        border-bottom: 2px solid #000;
        padding-bottom: 1px;
        line-height: 1.1;
    }
    .no-cell {
        text-align: center;
        padding: 3px 0 !important;
        font-size: 9.5px;
        border: 1px solid #000 !important;
    }

    .label-col {
        width: 24%;
        font-weight: bold;
        font-size: 9px;
        padding-top: 3.5px !important;
        padding-bottom: 3.5px !important;
    }
    .section-label {
        font-weight: normal;
        width: 24%;
        font-size: 9px;
    }
    .section-hint {
        text-align: center;
        font-style: italic;
        font-size: 8px;
    }

    .section-body {
        height: 55px;
        vertical-align: top !important;
        padding: 4px 6px !important;
        font-size: 9px;
        line-height: 1.25;
    }
    .item-cell {
        vertical-align: top !important;
        padding: 4px 6px !important;
        font-size: 9px;
        line-height: 1.3;
    }
    .target-body {
        height: 35px;
        vertical-align: top !important;
        padding: 4px 6px !important;
        font-size: 9px;
        line-height: 1.25;
    }

    .qty {
        text-align: center;
        vertical-align: middle !important;
        font-weight: bold;
        font-size: 10px;
    }

    .sign-table {
        width: 100%;
        margin-top: 8px;
        border-collapse: collapse;
        border: none !important;
        page-break-inside: avoid;
    }
    .sign-table td {
        border: none !important;
        vertical-align: top;
        padding: 0;
    }
    .sig-col { width: 23%; }
    .sig-gap { width: 2.66%; }
    .sig-box {
        border: 1px solid #000;
        background-color: #fff;
    }
    .sig-space {
        height: 42px;
        text-align: center;
        vertical-align: top;
        font-size: 8.5px;
        padding-top: 3px;
    }
    .sig-label {
        border-top: 1px solid #000;
        text-align: center;
        font-size: 8.5px;
        padding: 3px 0;
        background-color: #fff;
    }
</style>
</head>
<body>

<div class="sheet">
    <table class="main-table">

        {{-- HEADER --}}
        <tr>
            <td colspan="5" class="title-cell">
                <span class="bold-underline-title">Procurement Request Form</span>
            </td>
        </tr>
        <tr>
            <td colspan="5" class="no-cell">
                <strong>No.</strong>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <strong>{{ $procurement['no'] ?? '' }}</strong>
            </td>
        </tr>

        {{-- BASIC INFORMATION --}}
        @foreach($basicInfo as $row)
            <tr>
                <td class="label-col">{{ $row['label'] }}</td>
                <td colspan="4">{{ $row['value'] }}</td>
            </tr>
        @endforeach

        {{-- ORDER TYPE --}}
        <tr>
            <td class="label-col">ORDER TYPE</td>
            @foreach($orderTypeCells as $cell)
                <td style="width: {{ $cell['width'] }};">
                    {!! pr_pdf_checkmark($orderType[$cell['key']] ?? false) !!}
                    {{ $cell['label'] }}
                </td>
            @endforeach
        </tr>

        {{-- TEXT SECTIONS --}}
        @foreach($textSections as $section)
            <tr>
                <td class="section-label">{{ $section['label'] }}</td>
                <td colspan="4" class="section-hint">{{ $section['hint'] }}</td>
            </tr>
            <tr>
                <td colspan="5" class="section-body">{!! pr_pdf_nl2p($section['value']) !!}</td>
            </tr>
        @endforeach

        {{-- ─── PURCHASE ITEMS ─── --}}
        <tr>
            <th colspan="2" style="text-align: center; font-weight: normal; width: 44%;">
                Request purchase item
            </th>
            <th colspan="2" style="text-align: center; font-weight: normal; width: 40%;">
                (Is it match to our requirement?)
            </th>
            <th style="text-align: center; font-weight: normal; width: 16%;">
                Quantity
            </th>
        </tr>

        @forelse($items as $item)
            <tr>
                <td colspan="4" class="item-cell">
                    {{ $item['item'] ?? '-' }}
                </td>
                <td class="qty">{{ (int) $item['quantity'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="item-cell">&nbsp;</td>
                <td class="qty">&nbsp;</td>
            </tr>
        @endforelse

        {{-- TARGET PURCHASE --}}
        <tr>
            <td class="section-label">Target to purchase</td>
            <td colspan="4" class="section-hint">(Consider the install schedule)</td>
        </tr>
        <tr>
            <td colspan="5" class="target-body">{!! pr_pdf_nl2p($procurement['target_purchase'] ?? '') !!}</td>
        </tr>
    </table>

    {{-- SIGNATURES --}}
    <table class="sign-table">
        <tr>
            @foreach ($signatures as $sign)
                @php
                    $role = is_array($sign) ? ($sign['role'] ?? '') : $sign;
                    $name = is_array($sign) ? ($sign['name'] ?? '') : '';
                @endphp
                <td class="sig-col">
                    <div class="sig-box">
                        <div class="sig-space">{{ $name }}</div>
                        <div class="sig-label">{{ $role }}</div>
                    </div>
                </td>
                @if(!$loop->last)
                    <td class="sig-gap"></td>
                @endif
            @endforeach
        </tr>
    </table>
</div>

</body>
</html>