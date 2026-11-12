<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        
        // ═══ KOLOM BARU UNTUK DATA PR FINAL & TTD DIREKSI ═══
        'final_number',
        'budget_type',
        'reason_choose_vendor',
        'accounting_mgr_name',
        'cfo_name',
        'ceo_name',
        'is_accounting_approved',
        'is_cfo_approved',
        'is_ceo_approved',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'is_selected' => 'boolean',
        'is_accounting_approved' => 'boolean',
        'is_cfo_approved' => 'boolean',
        'is_ceo_approved' => 'boolean',
    ];

    /* ═══════════════════════════════════════════════════════════════
       RELATIONS
    ═══════════════════════════════════════════════════════════════ */

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    /* ═══════════════════════════════════════════════════════════════
       SCOPES
    ═══════════════════════════════════════════════════════════════ */

    public function scopeSelected($query)
    {
        return $query->where('is_selected', true);
    }

    public function scopeCheapest($query)
    {
        return $query->orderBy('total_price', 'asc');
    }
}