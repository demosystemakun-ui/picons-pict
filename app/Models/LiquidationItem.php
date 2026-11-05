<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiquidationItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'qty'   => 'integer',
        'price' => 'decimal:2',
    ];

    public function liquidation()
    {
        return $this->belongsTo(Liquidation::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->qty * $this->price;
    }
}