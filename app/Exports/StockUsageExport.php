<?php

namespace App\Exports;

use App\Models\DepartmentStock;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StockUsageExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    /** Threshold minimum stok */
    private const MIN_STOCK = 5;

    /** @var array<int, int> total used dalam periode per department_stock_id */
    protected array $usedInPeriod = [];

    /** @var array<int, int> total used all-time per department_stock_id */
    protected array $usedAllTime = [];

    /** @var array<int, int> user unik yang pakai per department_stock_id */
    protected array $uniqueUsers = [];

    public function __construct(
        protected string $startDate,
        protected string $endDate,
        protected ?int $departmentId = null
    ) {}

    public function collection(): Enumerable
    {
        $start = $this->startDate . ' 00:00:00';
        $end   = $this->endDate   . ' 23:59:59';

        // ── Ambil SEMUA DepartmentStock (bukan hanya yang terpakai) ──
        $stocks = DepartmentStock::with(['department', 'item'])
            ->when($this->departmentId, function ($q) {
                $q->where('department_id', $this->departmentId);
            })
            ->orderBy('department_id')
            ->orderBy('item_id')
            ->get();

        if ($stocks->isEmpty()) {
            return collect();
        }

        $stockIds = $stocks->pluck('id')->all();

        // ── Total pemakaian dalam PERIODE ──
        $this->usedInPeriod = DB::table('stock_usages')
            ->whereIn('department_stock_id', $stockIds)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('department_stock_id, SUM(quantity_used) as total')
            ->groupBy('department_stock_id')
            ->pluck('total', 'department_stock_id')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        // ── Total pemakaian ALL-TIME ──
        $this->usedAllTime = DB::table('stock_usages')
            ->whereIn('department_stock_id', $stockIds)
            ->selectRaw('department_stock_id, SUM(quantity_used) as total')
            ->groupBy('department_stock_id')
            ->pluck('total', 'department_stock_id')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        // ── Jumlah user unik yang memakai (dalam periode) ──
        $this->uniqueUsers = DB::table('stock_usages')
            ->whereIn('department_stock_id', $stockIds)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('department_stock_id, COUNT(DISTINCT user_id) as total')
            ->groupBy('department_stock_id')
            ->pluck('total', 'department_stock_id')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        return $stocks;
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
            'Status',
            'Terakhir Diperbarui',
        ];
    }

    public function map($stock): array
    {
        $sid = $stock->id;

        $sisa          = (int) $stock->stock;                              // sisa saat ini
        $usedAllTime   = $this->usedAllTime[$sid]  ?? 0;
        $usedInPeriod  = $this->usedInPeriod[$sid] ?? 0;
        $usersCount    = $this->uniqueUsers[$sid]  ?? 0;

        // Stok awal = sisa sekarang + SEMUA pemakaian all-time
        $stokAwal = $sisa + $usedAllTime;

        // Status berdasarkan sisa saat ini
        $status = $sisa <= self::MIN_STOCK ? 'NG' : 'OK';

        return [
            $stock->department->name     ?? '-',
            $stock->item->item_code      ?? '-',
            $stock->item->item_name      ?? '-',
            $stokAwal,
            $usedInPeriod,
            $sisa,
            $usersCount,
            $status,
            optional($stock->updated_at)->format('d M Y H:i') ?? '-',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $colStatus  = 'H'; // kolom Status

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

                foreach (range('A', $colStatus) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}