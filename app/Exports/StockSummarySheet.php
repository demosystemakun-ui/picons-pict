<?php

namespace App\Exports;

use App\Models\DepartmentStock;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StockSummarySheet implements FromArray, WithHeadings, WithTitle, WithEvents
{
    private const MIN_STOCK = 5;

    public function __construct(
        protected string $startDate,
        protected string $endDate,
        protected ?int $departmentId = null
    ) {}

    public function title(): string
    {
        return 'Ringkasan Stok';
    }

    public function headings(): array
    {
        return [
            'Departemen',
            'Kode Item',
            'Nama Item',
            'Stok Awal',
            'Terpakai (Periode)',
            'Sisa Stok',
            'Jumlah Pemakai',
            'Pemakai',
            'Status',
            'Terakhir Diperbarui',
        ];
    }

    public function array(): array
    {
        $start = $this->startDate . ' 00:00:00';
        $end   = $this->endDate   . ' 23:59:59';

        $stocks = DepartmentStock::with(['department', 'item'])
            ->when($this->departmentId, fn ($q) => $q->where('department_id', $this->departmentId))
            ->orderBy('department_id')
            ->orderBy('item_id')
            ->get();

        if ($stocks->isEmpty()) {
            return [];
        }

        $stockIds = $stocks->pluck('id')->all();

        // Terpakai dalam periode
        $usedInPeriod = DB::table('stock_usages')
            ->whereIn('department_stock_id', $stockIds)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('department_stock_id, SUM(quantity_used) as total')
            ->groupBy('department_stock_id')
            ->pluck('total', 'department_stock_id')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        // Terpakai all-time (untuk hitung stok awal)
        $usedAllTime = DB::table('stock_usages')
            ->whereIn('department_stock_id', $stockIds)
            ->selectRaw('department_stock_id, SUM(quantity_used) as total')
            ->groupBy('department_stock_id')
            ->pluck('total', 'department_stock_id')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        // User unik + nama per stock
        // PASTIKAN 'stock_usages.user_id' sesuai dengan nama kolom di database Anda
        $userAgg = DB::table('stock_usages')
            ->join('users', 'users.id', '=', 'stock_usages.user_id')
            ->whereIn('stock_usages.department_stock_id', $stockIds)
            ->whereBetween('stock_usages.created_at', [$start, $end])
            ->selectRaw('stock_usages.department_stock_id as sid, users.name as user_name')
            ->groupBy('sid', 'users.name')
            ->orderBy('sid')
            ->orderBy('user_name')
            ->get();

        $namesByStock = [];
        foreach ($userAgg as $row) {
            $namesByStock[(int) $row->sid][] = $row->user_name;
        }

        $rows = [];
        foreach ($stocks as $stock) {
            $sid        = $stock->id;
            $sisa       = (int) $stock->stock;
            $usedAll    = $usedAllTime[$sid]  ?? 0;
            $usedPeriod = $usedInPeriod[$sid] ?? 0;
            $userList   = $namesByStock[$sid] ?? [];

            $stokAwal = $sisa + $usedAll;
            $status   = $sisa <= self::MIN_STOCK ? 'NG' : 'OK';

            $rows[] = [
                $stock->department->name ?? '-',
                $stock->item->item_code  ?? '-',
                $stock->item->item_name  ?? '-',
                $stokAwal,
                $usedPeriod,
                $sisa,
                count($userList),
                $userList ? implode(', ', $userList) : '-',
                $status,
                optional($stock->updated_at)->format('d M Y H:i') ?? '-',
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $colStatus  = 'I';

                for ($row = 2; $row <= $highestRow; $row++) {
                    $cell = $colStatus . $row;
                    $val  = $sheet->getCell($cell)->getValue();

                    if ($val === 'NG') {
                        $sheet->getStyle($cell)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('FEE2E2');
                        $sheet->getStyle($cell)->getFont()
                            ->setBold(true)->getColor()->setRGB('B91C1C');
                    } elseif ($val === 'OK') {
                        $sheet->getStyle($cell)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('DCFCE7');
                        $sheet->getStyle($cell)->getFont()
                            ->setBold(true)->getColor()->setRGB('15803D');
                    }
                }

                foreach (range('A', 'J') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}