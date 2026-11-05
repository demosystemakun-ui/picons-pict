<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'procurement_request_id', 
        'vendor_id', 
        'po_number', 
        'order_date', 
        'estimated_arrival', 
        'status', 
        'notes'
    ];

    protected $casts = [
        'order_date'        => 'date',
        'estimated_arrival' => 'date',
    ];

    /* ═══════════════════════════════════════════════════════════════
       RELATIONS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Item-item di dalam PO ini.
     */
    public function items() 
    { 
        return $this->hasMany(PurchaseItem::class); 
    }

    /**
     * Vendor yang menerima PO ini.
     * Mengacu ke tabel procurement_vendors.
     */
    public function vendor() 
    { 
        return $this->belongsTo(ProcurementVendor::class, 'vendor_id'); 
    }

    /**
     * Procurement Request (Induk) yang menjadi dasar PO ini.
     */
    public function procurementRequest() 
    { 
        return $this->belongsTo(ProcurementRequest::class); 
    }

    /* ═══════════════════════════════════════════════════════════════
       SCOPES (Opsional)
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Scope: PO yang masih dalam status "ordered" (belum diterima).
     */
    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    /**
     * Scope: PO yang sudah selesai (barang diterima lengkap).
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: PO yang sudah diterima sebagian.
     */
    public function scopePartiallyReceived($query)
    {
        return $query->where('status', 'partially_received');
    }
}