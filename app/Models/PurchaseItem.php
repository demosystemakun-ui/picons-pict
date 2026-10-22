<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id', 'product_name', 'qty_ordered', 'qty_received', 'qty_damaged', 'unit_price'
    ];
}