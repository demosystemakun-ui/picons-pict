<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementRequestItem extends Model
{
protected $fillable = [
    'procurement_request_id',
    'item_name',
    'brand',
    'type',
    'model',
    'capacity',
    'specs',
    'item_requirement_note',
    'quantity',
    'unit',
    'picture',
];
}