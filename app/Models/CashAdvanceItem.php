<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAdvanceItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'qty'   => 'integer',
        'price' => 'decimal:2',
    ];

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->qty * $this->price;
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}