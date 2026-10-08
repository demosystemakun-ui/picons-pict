<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_request_id',
        'vendor_name',
        'item_name',
        'price',
        'quantity',
        'shipping_cost',
        'service_fee',
        'discount_voucher',
        'tax',
        'payment_method',
        'estimated_delivery',
        'total_price',
        'notes',
        'screenshot_path',
        'is_selected',
    ];

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }
}