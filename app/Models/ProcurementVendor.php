<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementVendor extends Model
{
    protected $fillable = [
        'procurement_request_id',
        'vendor_name',
        'marketplace',
        'company_name',
        'payment_method',
        'payment_bank',
        'estimated_delivery',
        'product_url',
        'notes',

        // Cost breakdown
        'subtotal',
        'shipping_cost',
        'discount_voucher',
        'tax_ppn',
        'total_before_tax',
        'total_price',
        'tax_note',

        // Meta
        'sort_order',
        'is_winner',
    ];

    protected $casts = [
        'subtotal'          => 'decimal:2',
        'shipping_cost'     => 'decimal:2',
        'discount_voucher'  => 'decimal:2',
        'tax_ppn'           => 'decimal:2',
        'total_before_tax'  => 'decimal:2',
        'total_price'       => 'decimal:2',
        'is_winner'         => 'boolean',
    ];

    /* ═══════════════════════════════════════════════════════════════
       RELATIONS
    ═══════════════════════════════════════════════════════════════ */

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    public function items()
    {
        return $this->hasMany(ProcurementVendorItem::class, 'procurement_vendor_id')
            ->orderBy('sort_order');
    }

    /* ═══════════════════════════════════════════════════════════════
       SCOPES
    ═══════════════════════════════════════════════════════════════ */

    public function scopeWinner($query)
    {
        return $query->where('is_winner', true);
    }

    public function scopeCheapest($query)
    {
        return $query->orderBy('total_price', 'asc');
    }
}