<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'item_id',
        'stock',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}