<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'vendor_type', 'address', 'phone', 'email', 'rating', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'rating' => 'decimal:2',
        ];
    }

    public function isMonotaro(): bool
    {
        return $this->vendor_type === 'monotaro';
    }
}
