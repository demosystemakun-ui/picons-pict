<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model
{
    protected $guarded = [];

    protected $casts = [
        'request_date' => 'date',
        'period_start' => 'date',
        'period_end'   => 'date',
        'signers'      => 'array',
    ];

    public const DEFAULT_SIGNERS = [
        'requested'   => ['name' => '',                'title' => 'Internship'],
        'checked'     => ['name' => 'Fumiya Ota',      'title' => 'Manager Operational'],
        'acknowledge' => ['name' => 'Takumi Nibe',     'title' => 'COO'],
        'verified'    => ['name' => 'Ahmad Rizaldi',   'title' => 'CFO'],
        'approved'    => ['name' => 'Hiroyuki Yazawa', 'title' => 'President Director'],
    ];

    public function items()
    {
        return $this->hasMany(ReimbursementItem::class);
    }

    public function getTotalAttribute(): int
    {
        return $this->items->sum(fn ($i) => $i->qty * $i->price);
    }

    /** Nomor otomatis: 001/OPS-PICT/CRF/IX/2026 */
    public static function generateNumber($date = null): string
    {
        $date  = $date ? \Carbon\Carbon::parse($date) : now();
        $roman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$date->month];
        $seq   = static::whereYear('request_date', $date->year)->count() + 1;

        return sprintf('%03d/OPS-PICT/CRF/%s/%d', $seq, $roman, $date->year);
    }
}
