<?php

namespace App\Exports;

use App\Models\StockUsage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockUsageExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected string $startDate,
        protected string $endDate,
        protected ?int $departmentId = null
    ) {}

    public function collection()
    {
        return StockUsage::with([
                'user',
                'user.department',
                'departmentStock.item',
                'departmentStock.department',
            ])
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->when($this->departmentId, function ($q) {
                $q->whereHas('departmentStock', fn ($q2) => $q2->where('department_id', $this->departmentId));
            })
            ->orderBy('created_at', 'desc')
            ->get();
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
        ];
    }

    public function map($usage): array
    {
        return [
            $usage->created_at->format('d M Y H:i'),
            $usage->departmentStock->department->name ?? '-',
            $usage->departmentStock->item->item_code  ?? '-',
            $usage->departmentStock->item->item_name  ?? '-',
            $usage->user->name                        ?? 'User dihapus',
            $usage->user->department->name            ?? '-',
            $usage->quantity_used,
        ];
    }
}