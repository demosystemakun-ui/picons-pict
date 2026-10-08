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
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'is_selected' => 'boolean',
    ];

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }
}