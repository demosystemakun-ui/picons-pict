<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    protected $fillable = [
        'department_stock_id', 'user_id', 'quantity_in', 'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function departmentStock()
    {
        return $this->belongsTo(DepartmentStock::class);
    }
}