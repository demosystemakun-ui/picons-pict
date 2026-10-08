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
        'image_url',
        'quantity',
        'unit_price',
        'subtotal',
        'stock_status',
        'weight',
        'sort_order',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
        'weight'     => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(ProcurementVendor::class, 'procurement_vendor_id');
    }
}