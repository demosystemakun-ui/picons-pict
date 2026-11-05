<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model
{
    // Menggunakan guarded agar semua field bisa diisi massal
    protected $guarded = [];

    protected $casts = [
        'request_date' => 'date',
        'period_start' => 'date',
        'period_end'   => 'date',
        'approved_at'  => 'datetime',
        'signers'      => 'array',
    ];

    public const DEFAULT_SIGNERS = [
        'requested'   => ['name' => '',                'title' => 'Internship'],
        'checked'     => ['name' => 'Fumiya Ota',      'title' => 'Manager Operational'],
        'acknowledge' => ['name' => 'Takumi Nibe',     'title' => 'COO'],
        'verified'    => ['name' => 'Ahmad Rizaldi',   'title' => 'CFO'],
        'approved'    => ['name' => 'Hiroyuki Yazawa', 'title' => 'President Director'],
    ];

    /* ═══════════════════════════════════════════════════════════════
       RELATIONS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Relasi ke item-item reimbursement.
     */
    public function items()
    {
        return $this->hasMany(ReimbursementItem::class);
    }

    /* ═══════════════════════════════════════════════════════════════
       ACCESSORS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Total nominal reimbursement (dari qty * price tiap item).
     */
    public function getTotalAttribute(): int
    {
        return $this->items->sum(fn ($i) => $i->qty * $i->price);
    }

    /**
     * Total dalam format Rupiah.
     */
    public function getTotalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /* ═══════════════════════════════════════════════════════════════
       HELPERS
    ═══════════════════════════════════════════════════════════════ */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Generate nomor otomatis (Draft) — HANYA placeholder.
     * Nomor Final akan diinput manual oleh Admin saat approve.
     */
    public static function generateNumber($date = null): string
    {
        $date  = $date ? \Carbon\Carbon::parse($date) : now();
        $roman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$date->month];

        // Hitung sequence dari SEMUA data tahun berjalan (bukan hanya approved)
        // agar nomor draft tidak bentrok.
        $seq = static::whereYear('request_date', $date->year)->count() + 1;

        return sprintf('DRAFT-%03d/OPS-PICT/CRF/%s/%d', $seq, $roman, $date->year);
    }

    /* ═══════════════════════════════════════════════════════════════
       SCOPES
    ═══════════════════════════════════════════════════════════════ */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForDepartment($query, $departmentName)
    {
        return $query->where('department', $departmentName);
    }
}