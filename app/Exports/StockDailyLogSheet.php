<?php

namespace App\Exports;

use App\Models\DepartmentStock;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StockDailyLogSheet implements FromArray, WithTitle, WithEvents
{
    private const MIN_STOCK = 5;

    private int   $daysInMonth;
    private int   $startDay;
    private int   $endDay;
    private array $log = [];   // [stockId => [day => ['u'=>, 'i'=>, 'stock'=>]]]

    public function __construct(
        protected string $startDate,
        protected string $endDate,
        protected ?int $departmentId = null
    ) {}

    public function title(): string
    {
        return 'Daily Log';
    }

    public function array(): array
    {
        $start    = \Carbon\Carbon::parse($this->startDate);
        $end      = \Carbon\Carbon::parse($this->endDate);
        $year     = $start->year;
        $month    = $start->month;
        $this->daysInMonth = $start->daysInMonth;
        $this->startDay    = $start->day;
        $this->endDay      = ($end->year === $year && $end->month === $month)
            ? $end->day
            : $this->daysInMonth;

        // ── Ambil semua stok ──
        $stocks = DepartmentStock::with(['department', 'item'])
            ->when($this->departmentId, fn ($q) => $q->where('department_id', $this->departmentId))
            ->orderBy('department_id')
            ->orderBy('item_id')
            ->get();

        if ($stocks->isEmpty()) {
            return [['Tidak ada data stok untuk periode ini.']];
        }

        $stockIds = $stocks->pluck('id')->all();

        // ── Ambil USE per hari ──
        $uses = DB::table('stock_usages')
            ->whereIn('department_stock_id', $stockIds)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('department_stock_id, DAY(created_at) as day, SUM(quantity_used) as total')
            ->groupBy('department_stock_id', 'day')
            ->get();

        // ── Ambil IN per hari ──
        $ins = DB::table('stock_ins')
            ->whereIn('department_stock_id', $stockIds)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('department_stock_id, DAY(created_at) as day, SUM(quantity_in) as total')
            ->groupBy('department_stock_id', 'day')
            ->get();

        // ── Prev stock: hitung dari current stock ──
        // prevStock = currentStock + totalUsed (all time) - totalIn (all time)
        $totalUsedAll = DB::table('stock_usages')
            ->whereIn('department_stock_id', $stockIds)
            ->selectRaw('department_stock_id, SUM(quantity_used) as total')
            ->groupBy('department_stock_id')
            ->pluck('total', 'department_stock_id')
            ->map(fn ($v) => (int) $v)->toArray();

        $totalInAll = DB::table('stock_ins')
            ->whereIn('department_stock_id', $stockIds)
            ->selectRaw('department_stock_id, SUM(quantity_in) as total')
            ->groupBy('department_stock_id')
            ->pluck('total', 'department_stock_id')
            ->map(fn ($v) => (int) $v)->toArray();

        // Build matrix harian
        $dailyU = [];
        $dailyI = [];
        foreach ($uses as $r) {
            $dailyU[(int) $r->department_stock_id][(int) $r->day] = (int) $r->total;
        }
        foreach ($ins as $r) {
            $dailyI[(int) $r->department_stock_id][(int) $r->day] = (int) $r->total;
        }

        // ── Header ──
        $headerRow1 = ['No', 'Consumable Item', 'Unit', 'Prev. Stock'];
        $headerRow2 = ['',   '',                 '',     ''];
        for ($d = 1; $d <= $this->daysInMonth; $d++) {
            $headerRow1[] = (string) $d;
            $headerRow1[] = '';
            $headerRow1[] = '';
            $headerRow2[] = 'U';
            $headerRow2[] = 'I';
            $headerRow2[] = 'Stock';
        }
        $headerRow1[] = 'Total Usage';
        $headerRow1[] = 'Standard Stock';
        $headerRow1[] = 'Current Stock';
        $headerRow1[] = 'Status';
        $headerRow2[] = '';
        $headerRow2[] = '';
        $headerRow2[] = '';
        $headerRow2[] = '';

        $rows = [$headerRow1, $headerRow2];

        // ── Data per item ──
        $no = 1;
        foreach ($stocks as $stock) {
            $sid       = $stock->id;
            $current   = (int) $stock->stock;
            $usedAll   = $totalUsedAll[$sid] ?? 0;
            $inAll     = $totalInAll[$sid]   ?? 0;
            $prevStock = $current + $usedAll - $inAll;

            $row = [
                $no++,
                $stock->item->item_name ?? '-',
                $stock->item->unit->name ?? ($stock->item->unit ?? '-'),
                $prevStock,
            ];

            $running = $prevStock;
            $totalUsage = 0;

            for ($d = 1; $d <= $this->daysInMonth; $d++) {
                $u = $dailyU[$sid][$d] ?? 0;
                $i = $dailyI[$sid][$d] ?? 0;

                if ($d < $this->startDay || $d > $this->endDay) {
                    // Di luar rentang — kosongkan
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                    continue;
                }

                $running = $running + $i - $u;
                $totalUsage += $u;

                $row[] = $u;
                $row[] = $i;
                $row[] = $running;
            }

            $row[] = $totalUsage;
            $row[] = (int) ($stock->item->minimum_stock ?? 0);
            $row[] = $current;
            $row[] = $current <= self::MIN_STOCK ? 'NG' : 'OK';

            $rows[] = $row;
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();
                $colCount   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

                // Merge header tanggal (baris 1 kolom U-I-Stock)
                for ($d = 1; $d <= $this->daysInMonth; $d++) {
                    $startCol = 5 + (($d - 1) * 3); // mulai kolom ke-5 (E)
                    $endCol   = $startCol + 2;
                    $startLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol);
                    $endLet   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endCol);
                    $sheet->mergeCells("{$startLet}1:{$endLet}1");
                }

                // Merge header tetap (No, Item, Unit, Prev.Stock di baris 1-2)
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:B2');
                $sheet->mergeCells('C1:C2');
                $sheet->mergeCells('D1:D2');

                // Header summary (Total Usage, Standard Stock, Current Stock, Status)
                $summaryCols = [
                    5 + ($this->daysInMonth * 3),
                    5 + ($this->daysInMonth * 3) + 1,
                    5 + ($this->daysInMonth * 3) + 2,
                    5 + ($this->daysInMonth * 3) + 3,
                ];
                foreach ($summaryCols as $sc) {
                    $let = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($sc);
                    $sheet->mergeCells("{$let}1:{$let}2");
                }

                // Style header
                $headerRange = 'A1:' . $highestCol . '2';
                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('1F4E79');
                $sheet->getStyle($headerRange)->getFont()
                    ->setBold(true)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle($headerRange)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Style border semua sel data
                $dataRange = 'A1:' . $highestCol . $highestRow;
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Highlight kolom U (biru) & I (kuning muda) bergantian
                for ($d = 1; $d <= $this->daysInMonth; $d++) {
                    $uCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(5 + (($d - 1) * 3));
                    $iCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(6 + (($d - 1) * 3));
                    $sCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + (($d - 1) * 3));

                    $sheet->getStyle("{$uCol}1:{$uCol}{$highestRow}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
                    $sheet->getStyle("{$iCol}1:{$iCol}{$highestRow}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');
                    $sheet->getStyle("{$sCol}1:{$sCol}{$highestRow}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA');
                }

                // Highlight kolom Status NG/OK
                $statusCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);
                for ($row = 3; $row <= $highestRow; $row++) {
                    $cell = "{$statusCol}{$row}";
                    $val  = $sheet->getCell($cell)->getValue();
                    if ($val === 'NG') {
                        $sheet->getStyle($cell)->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');
                        $sheet->getStyle($cell)->getFont()
                            ->setBold(true)->getColor()->setRGB('B91C1C');
                    } elseif ($val === 'OK') {
                        $sheet->getStyle($cell)->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
                        $sheet->getStyle($cell)->getFont()
                            ->setBold(true)->getColor()->setRGB('15803D');
                    }
                }

                // Freeze pane: kolom No-Item-Unit-Prev.Stock + baris header
                $sheet->freezePane('E3');

                // Lebar kolom
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(8);
                $sheet->getColumnDimension('D')->setWidth(10);
                for ($i = 5; $i <= $colCount; $i++) {
                    $sheet->getColumnDimension(
                        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i)
                    )->setWidth(6);
                }
            },
        ];
    }
}