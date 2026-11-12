<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementComparison extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'total_price' => 'decimal:2',
        'is_selected' => 'boolean',
        'is_accounting_approved' => 'boolean',
        'is_cfo_approved' => 'boolean',
        'is_ceo_approved' => 'boolean',
    ];

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }
}