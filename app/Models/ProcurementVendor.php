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
       MASTER VENDOR & SAW HELPERS
    ═══════════════════════════════════════════════════════════════ */

    public function matchedMasterVendor(): ?Vendor
    {
        $names = array_filter([
            strtolower(trim((string) $this->vendor_name)),
            strtolower(trim((string) $this->marketplace)),
        ]);

        if (empty($names)) {
            return null;
        }

        $exactMatch = Vendor::query()
            ->where('is_active', true)
            ->where(function ($q) use ($names) {
                foreach ($names as $name) {
                    $q->orWhereRaw('LOWER(name) = ?', [$name])
                      ->orWhereRaw('LOWER(vendor_type) = ?', [$name]);
                }
            })
            ->first();

        if ($exactMatch) {
            return $exactMatch;
        }

        $primaryName = $names[0];

        return Vendor::query()
            ->where('is_active', true)
            ->where(function ($q) use ($primaryName) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $primaryName . '%'])
                  ->orWhereRaw('LOWER(vendor_type) LIKE ?', ['%' . $primaryName . '%']);
            })
            ->first();
    }

    public function getSawRatingAttribute(): float
    {
        $master = $this->matchedMasterVendor();

        if ($master && is_numeric($master->rating) && (float) $master->rating > 0) {
            return min(max((float) $master->rating, 0.0), 5.0);
        }

        if (!empty($this->notes) && preg_match('/rating[:\s]*(\d+(?:[.,]\d+)?)/i', $this->notes, $matches)) {
            return min(max((float) str_replace(',', '.', $matches[1]), 0.0), 5.0);
        }

        return 4.0;
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