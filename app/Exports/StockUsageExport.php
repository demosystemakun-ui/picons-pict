<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StockUsageExport implements WithMultipleSheets
{
    public function __construct(
        protected string $startDate,
        protected string $endDate,
        protected ?int $departmentId = null
    ) {}

    public function sheets(): array
    {
        return [
            'Ringkasan Stok'    => new StockSummarySheet(
                $this->startDate,
                $this->endDate,
                $this->departmentId
            ),
            'History Pemakaian' => new StockUsageHistorySheet(
                $this->startDate,
                $this->endDate,
                $this->departmentId
            ),
        ];
    }
}