<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lampiran Bukti Gambar Harga - {{ $procurement->no }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 24px;
            color: #111827;
            background: #ffffff;
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .btn-print {
            padding: 8px 16px;
            background: #0d9488;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-print:hover {
            background: #0f766e;
        }
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .doc-title {
            margin: 0 0 6px;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .doc-meta {
            font-size: 12px;
            color: #374151;
            line-height: 1.55;
        }
        .evidence-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .evidence-card {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px;
            background: #ffffff;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .evidence-image-box {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 240px;
            max-height: 360px;
            background: #f9fafb;
            border: 1px solid #f3f4f6;
            border-radius: 6px;
            padding: 6px;
            overflow: hidden;
        }
        .evidence-image-box img {
            max-width: 100%;
            max-height: 340px;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        .evidence-caption {
            margin-top: 8px;
            font-size: 11px;
            color: #4b5563;
            line-height: 1.45;
        }
        .evidence-caption strong {
            color: #111827;
        }
        .empty-state {
            padding: 28px;
            text-align: center;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            font-size: 13px;
            color: #6b7280;
        }
        @media print {
            .toolbar { display: none; }
            body { margin: 0; }
            .evidence-grid { gap: 12px; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="doc-meta">
            <strong>Dokumen Lampiran:</strong> Bukti Screenshot Harga Vendor
        </div>
        <button type="button" onclick="window.print()" class="btn-print">
            Cetak Lampiran Bukti Gambar
        </button>
    </div>

    <div class="doc-header">
        <div>
            <h1 class="doc-title">LAMPIRAN BUKTI SCREENSHOT HARGA VENDOR</h1>
            <div class="doc-meta">
                <div><strong>PR Awal:</strong> {{ $procurement->no }} — {{ $procurement->description }}</div>
                @if($procurement->finalComparison?->final_number)
                    <div><strong>PR Final:</strong> {{ $procurement->finalComparison->final_number }}</div>
                @endif
                @if($procurement->winnerVendor)
                    <div>
                        <strong>Vendor Terpilih:</strong> {{ $procurement->winnerVendor->vendor_name }}
                        (Rp {{ number_format((float) $procurement->winnerVendor->total_price, 0, ',', '.') }})
                    </div>
                @endif
            </div>
        </div>
        <div class="doc-meta" style="text-align: right;">
            <div><strong>Jumlah Lampiran:</strong> {{ $procurement->attachments->count() }} gambar</div>
            <div><strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    @if($procurement->attachments->count() > 0)
        <div class="evidence-grid">
            @foreach($procurement->attachments as $index => $att)
                <div class="evidence-card">
                    <div class="evidence-image-box">
                        <img src="{{ $att->url }}" alt="{{ $att->original_name ?? 'Bukti Harga #' . ($index + 1) }}">
                    </div>
                    <div class="evidence-caption">
                        <div>
                            <strong>Lampiran #{{ $index + 1 }}</strong>
                            — Vendor: <strong>{{ $att->vendor?->vendor_name ?? strtoupper($att->mode ?? 'Vendor') }}</strong>
                        </div>
                        <div>Nama File: {{ $att->original_name ?? basename($att->file_path) }}</div>
                        <div>Diunggah: {{ optional($att->created_at)->format('d/m/Y H:i') ?: '-' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            Belum ada lampiran gambar bukti harga yang tersimpan untuk pengajuan ini.
        </div>
    @endif
</body>
</html>