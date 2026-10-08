<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date_from' => 'date',
        'date_to'   => 'date',
    ];

    public function reimbursement()
    {
        return $this->belongsTo(Reimbursement::class);
    }

    public function getTotalAttribute(): int
    {
        return $this->qty * $this->price;
    }

    /** "1 Sep - 30 Sep 2026" / "1 Sep 2026"; fallback ke date_label untuk data lama */
    public function getDateDisplayAttribute(): string
    {
        if (!$this->date_from) {
            return (string) $this->date_label;
        }

        $from = $this->date_from;
        $to   = $this->date_to;

        if (!$to || $from->isSameDay($to)) {
            return $from->translatedFormat('j M Y');
        }

        return $from->year === $to->year
            ? $from->translatedFormat('j M') . ' - ' . $to->translatedFormat('j M Y')
            : $from->translatedFormat('j M Y') . ' - ' . $to->translatedFormat('j M Y');
    }
}