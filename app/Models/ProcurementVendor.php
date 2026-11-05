<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementVendorItem extends Model
{
    protected $fillable = [
        'procurement_vendor_id',
        'product_name',
        'variant',
        'sku',
        'quantity',
        'unit_price',
        'subtotal',
        'stock_status',
        'weight',
        'sort_order',
        'picture', // jika ada
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
        'weight'     => 'decimal:2',
    ];

    /**
     * Relasi balik ke ProcurementVendor.
     */
    public function procurementVendor()
    {
        return $this->belongsTo(ProcurementVendor::class);
    }
}