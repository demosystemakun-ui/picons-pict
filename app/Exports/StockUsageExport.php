<?php

namespace App\Exports;

use App\Models\StockLog;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockUsageExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle,
    ShouldAutoSize
{
    protected ?string $startDate;
    protected ?string $endDate;
    protected ?int $departmentId;

    public function __construct(?string $startDate = null, ?string $endDate = null, ?int $departmentId = null)
    {
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
        $this->departmentId = $departmentId;
    }

    /**
     * Query dari tabel stock_logs.
     * Return type Enumerable/Collection WAJIB agar sesuai interface FromCollection di PHP 8.5.
     */
   public function collection()
{
    return StockUsage::with(['user', 'user.department', 'departmentStock.item', 'departmentStock.department'])
        ->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59'])
        ->when($this->departmentId, fn ($q) => $q->whereHas('departmentStock', fn ($q2) => $q2->where('department_id', $this->departmentId)))
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($u) {
            return [
                'Tanggal'      => $u->created_at->format('d M Y H:i'),
                'Departemen'   => $u->departmentStock->department->name ?? '-',
                'Kode Item'    => $u->departmentStock->item->item_code ?? '-',
                'Nama Item'    => $u->departmentStock->item->item_name ?? '-',
                'Pemakai'      => $u->user->name ?? 'User dihapus',
                'Dept Pemakai' => $u->user->department->name ?? '-',
                'Jumlah Pakai' => $u->quantity_used,
            ];
        });
}
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Jam',
            'Kode Barang',
            'Nama Barang',
            'Departemen',
            'Jumlah Terpakai',
            'Satuan',
            'Diproses Oleh',
            'Keterangan',
            'Terakhir Update',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            optional($row->created_at)->format('d/m/Y'),
            optional($row->created_at)->format('H:i'),
            $row->item->item_code ?? '-',
            $row->item->item_name ?? '-',
            $row->department->name ?? '-',
            $row->quantity ?? 0,
            $row->item->unit->code ?? 'Unit',
            $row->user->name ?? '-',
            $row->notes ?? '-',
            optional($row->updated_at)->format('d/m/Y H:i'),
        ];
    }

   public function styles(Worksheet $sheet): array
{
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $lastRow = $sheet->getHighestRow();

        foreach (['A', 'B', 'C', 'G'] as $col) {
            $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                  ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getStyle("A1:K{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 12,
            'C' => 8,
            'D' => 15,
            'E' => 35,
            'F' => 25,
            'G' => 15,
            'H' => 10,
            'I' => 20,
            'J' => 30,
            'K' => 18,
        ];
    }

    public function title(): string
    {
        return 'Stock Usage Report';
    }
}