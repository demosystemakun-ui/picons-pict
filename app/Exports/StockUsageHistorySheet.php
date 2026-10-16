<?php

namespace App\Exports;

use App\Models\StockUsage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class StockUsageHistorySheet implements FromArray, WithHeadings, WithTitle, WithEvents
{
    public function __construct(
        protected string $startDate,
        protected string $endDate,
        protected ?int $departmentId = null
    ) {}

    public function title(): string
    {
        return 'History Pemakaian';
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Departemen Stok',
            'Kode Item',
            'Nama Item',
            'Pemakai',
            'Departemen Pemakai',
            'Jumlah Pakai',
            'Sisa Stok Setelah Pakai',
            'Keterangan',
        ];
    }

    public function array(): array
    {
        $start = $this->startDate . ' 00:00:00';
        $end   = $this->endDate   . ' 23:59:59';

        // Ambil semua usage dalam periode, urut kronologis ASC agar bisa hitung sisa per transaksi
        $usages = StockUsage::with([
                'user',
                'user.department',
                'departmentStock.item',
                'departmentStock.department',
            ])
            ->whereBetween('created_at', [$start, $end])
            ->when($this->departmentId, function ($q) {
                $q->whereHas('departmentStock', fn ($q2) => $q2->where('department_id', $this->departmentId));
            })
            ->orderBy('department_stock_id')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($usages->isEmpty()) {
            return [];
        }

        // Untuk hitung sisa stok setelah pakai di tiap transaksi
        // Kita perlu stok awal per department_stock_id.
        // Stok awal = sisa sekarang + total pemakaian all-time.
        $stockIds = $usages->pluck('department_stock_id')->unique()->values()->all();

        $usedAllTime = \DB::table('stock_usages')
            ->whereIn('department_stock_id', $stockIds)
            ->selectRaw('department_stock_id, SUM(quantity_used) as total')
            ->groupBy('department_stock_id')
            ->pluck('total', 'department_stock_id')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        $currentStock = \DB::table('department_stocks')
            ->whereIn('id', $stockIds)
            ->pluck('stock', 'id')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        $initialStock = [];
        foreach ($stockIds as $sid) {
            $initialStock[$sid] = ($currentStock[$sid] ?? 0) + ($usedAllTime[$sid] ?? 0);
        }

        // Hitung sisa setelah tiap transaksi (running)
        $runningUsed = [];
        $rows = [];

        foreach ($usages as $u) {
            $sid = $u->department_stock_id;
            $runningUsed[$sid] = ($runningUsed[$sid] ?? 0) + (int) $u->quantity_used;
            $sisaSetelah = max(($initialStock[$sid] ?? 0) - $runningUsed[$sid], 0);

            $rows[] = [
                $u->created_at->format('d M Y H:i'),
                $u->departmentStock->department->name ?? '-',
                $u->departmentStock->item->item_code  ?? '-',
                $u->departmentStock->item->item_name  ?? '-',
                $u->user->name                        ?? 'User dihapus',
                $u->user->department->name            ?? '-',
                (int) $u->quantity_used,
                $sisaSetelah,
                $u->note ?? '-',   // jika ada kolom note; kalau tidak ada, hapus baris ini
            ];
        }

        // Balik DESC untuk tampilan (terbaru di atas)
        usort($rows, fn ($a, $b) => strcmp($b[0], $a[0]));

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Freeze baris header
                $sheet->freezePane('A2');
            },
        ];
    }
}