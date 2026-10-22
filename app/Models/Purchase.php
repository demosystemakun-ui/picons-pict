<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'procurement_request_id', 'vendor_id', 'po_number', 'order_date', 
        'estimated_arrival', 'status', 'notes'
    ];

    public function items() { return $this->hasMany(PurchaseItem::class); }
    public function vendor() { return $this->belongsTo(ProcurementVendor::class, 'vendor_id'); }
    public function procurementRequest() { return $this->belongsTo(ProcurementRequest::class); }
}