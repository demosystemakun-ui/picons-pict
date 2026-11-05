<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Liquidation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'request_date' => 'date',
        'approved_at'  => 'datetime',
        'signers'      => 'array',
    ];

    public function items()
    {
        return $this->hasMany(LiquidationItem::class);
    }

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    public function getTotalAttribute(): int
    {
        return $this->items->sum(fn ($i) => $i->qty * $i->price);
    }

    public function getTotalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }

    public static function generateNumber($date = null): string
    {
        $date  = $date ? \Carbon\Carbon::parse($date) : now();
        $roman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$date->month];
        $seq   = static::whereYear('request_date', $date->year)->count() + 1;

        return sprintf('DRAFT-LQ-%03d/OPS-PICT/%s/%d', $seq, $roman, $date->year);
    }
}