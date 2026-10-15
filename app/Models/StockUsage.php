<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockUsage extends Model
{
    protected $fillable = [
        'department_stock_id',
        'user_id',
        'quantity_used',
    ];

    public function departmentStock()
    {
        return $this->belongsTo(DepartmentStock::class, 'department_stock_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}