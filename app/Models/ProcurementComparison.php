<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ⚠️ LEGACY MODEL
 * Model ini adalah versi lama dari perbandingan vendor.
 * Untuk fitur baru, gunakan ProcurementVendor.
 */
class ProcurementComparison extends Model
{
    protected $fillable = [
        'procurement_request_id',
        'vendor_name',
        'company_name',
        'total_price',
        'tax_note',
        'sort_order',
        'is_selected',
        'reason',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'is_selected' => 'boolean',
    ];

    /* ═══════════════════════════════════════════════════════════════
       RELATIONS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Relasi ke Procurement Request (Induk).
     */
    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    /* ═══════════════════════════════════════════════════════════════
       SCOPES
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Scope: Hanya ambil vendor yang sudah dipilih (pemenang).
     */
    public function scopeSelected($query)
    {
        return $query->where('is_selected', true);
    }

    /**
     * Scope: Urutkan dari harga termurah.
     */
    public function scopeCheapest($query)
    {
        return $query->orderBy('total_price', 'asc');
    }
}