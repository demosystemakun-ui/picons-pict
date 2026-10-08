<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Consumable Request - {{ $consumable['no'] ?? '' }}</title>
    <style>
        /* ═══ SCREEN STYLE ═══ */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #f3f4f6;
        }

        .container {
            max-width: 900px;
            margin: auto;
            border: 1px solid #000;
            padding: 0;
            background: #fff;
        }

        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #000;
            padding: 10px 15px;
        }
        .header .logo { flex-shrink: 0; }
        .header .title { flex-grow: 1; text-align: center; }
        .header h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info {
            padding: 10px 15px;
            border-bottom: 1px solid #000;
        }
        .info table { width: 100%; font-size: 12px; }
        .info td { padding: 2px 0; }

        .table-items {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .table-items th,
        .table-items td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }
        .table-items th {
            background: #f0f0f0;
            text-align: center;
        }

        .note-red {
            padding: 10px 15px;
            border-top: 1px solid #000;
            font-weight: bold;
            color: #C00000;
            text-transform: uppercase;
            font-size: 11px;
        }
        .note {
            padding: 10px 15px;
            border-top: 1px solid #000;
            font-size: 12px;
        }

        .signature {
            padding: 15px;
        }
        .signature table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 12px;
        }
        .signature th,
        .signature td {
            border: 1px solid #000;
            padding: 8px;
        }
        .signature th { background: #f0f0f0; }
        .signature td { height: 60px; vertical-align: bottom; font-weight: bold; }

        /* ═══ PRINT STYLE ═══ */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            body {
                padding: 0 !important;
                margin: 0 !important;
                background: #fff !important;
            }

            .container {
                max-width: none !important;
                margin: 0 !important;
                border: 1px solid #000 !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

<div class="container">

    {{-- HEADER --}}
    <div class="header">
        <div class="logo">
            <img src="{{ asset('logo/pict.png') }}"
                 alt="Logo PICT"
                 style="max-height: 45px; width: auto; display: block;">
        </div>
        <div class="title">
            <h2>CONSUMABLE REQUEST</h2>
        </div>
    </div>

    {{-- INFO --}}
    <div class="info">
        <table>
            <tr>
                <td style="width: 80px; font-weight: bold;">Date</td>
                <td style="width: 10px;">:</td>
                <td>{{ $consumable['date'] }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Subjek</td>
                <td>:</td>
                <td>{{ $consumable['description'] }}</td>
            </tr>
        </table>
    </div>

    {{-- TABEL ITEM --}}
    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 30px;">No.</th>
                <th>Item</th>
                <th style="width: 35%;">Purpose</th>
                <th style="width: 60px;">Quantity</th>
                <th style="width: 60px;">Unit</th>
                <th style="width: 100px;">Picture</th>
            </tr>
        </thead>
        <tbody>
            @forelse($consumable['purchase_items'] ?? [] as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>

                    {{-- ═══ KOLOM ITEM — gabungkan field terpisah jadi teks ═══ --}}
                    <td>
                        @php
                            $details = [];

                            // Field baru (struktur detail)
                            if (!empty($item['item_name']))  $details[] = 'Item: ' . $item['item_name'];
                            if (!empty($item['brand']))      $details[] = 'Brand: ' . $item['brand'];
                            if (!empty($item['type']))       $details[] = 'Type: ' . $item['type'];
                            if (!empty($item['model']))      $details[] = 'Model: ' . $item['model'];
                            if (!empty($item['capacity']))   $details[] = 'Capacity: ' . $item['capacity'];
                            if (!empty($item['specs']))      $details[] = $item['specs'];

                            // Fallback: data lama (item_details)
                            if (empty($details) && !empty($item['item_details'])) {
                                $details[] = $item['item_details'];
                            }
                        @endphp

                        @if(count($details))
                            {!! nl2br(e(implode("\n", $details))) !!}
                        @else
                            -
                        @endif
                    </td>

                    {{-- ═══ KOLOM PURPOSE ═══ --}}
                    <td>{!! nl2br(e($item['requirement'] ?? '-')) !!}</td>

                    {{-- ═══ QUANTITY ═══ --}}
                    <td style="text-align: center;">
                        {{ rtrim(rtrim(number_format((float) ($item['quantity'] ?? 0), 2, '.', ''), '0'), '.') }}
                    </td>

                    {{-- ═══ UNIT ═══ --}}
                    <td style="text-align: center;">{{ $item['unit'] ?? 'Pcs' }}</td>

                    {{-- ═══ PICTURE ═══ --}}
                    <td style="text-align: center;">
                        @if(!empty($item['picture']))
                            <img src="{{ Storage::url($item['picture']) }}"
                                 alt="Picture"
                                 style="max-width: 80px; max-height: 80px;">
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">Tidak ada item.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- NOTE MERAH --}}
    <div class="note-red">
        NOTE : JIKA INGIN ORDER KEMBALI SEPERTI SARUNG TANGAN, SPIDOL, SAPU, PENGKI, HARUS
        MENGURANGI BEKAS YANG TIDAK TERPAKAI
        (IF YOU REPEAT ORDER THE GLOVES, SPIDOL, BROOM, PENGKI, MUST BE RETURN UNUSED ITEM)
    </div>

    {{-- NOTE BIASA --}}
    <div class="note">
        <strong>Note :</strong> {{ $consumable['notes'] ?? 'Request to Elisa' }}
    </div>

    {{-- TANDA TANGAN --}}
    <div class="signature">
        <table>
            <tr>
                <th style="width: 33%;">Request By</th>
                <th style="width: 33%;">Approved By</th>
                <th style="width: 33%;">Verified By</th>
            </tr>
            <tr>
                <td>{{ $consumable['request_by'] ?? '' }}</td>
                <td>{{ $consumable['approved_by'] ?? '' }}</td>
                <td>{{ $consumable['verified_by'] ?? '' }}</td>
            </tr>
        </table>
    </div>

</div>

{{-- ═══ AUTO PRINT SCRIPT ═══ --}}
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