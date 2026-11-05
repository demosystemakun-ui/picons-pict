<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementRequestItem extends Model
{
    protected $fillable = [
        'procurement_request_id',
        'consumable_request_item_id', // 🌟 BARU: Traceability ke Consumable Request
        'department_id',              // 🌟 BARU: Untuk Distribusi Stok
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

    /* ═══════════════════════════════════════════════════════════════
       RELATIONS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Relasi ke Procurement Request (Induk).
     */
    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    /**
     * Relasi ke Consumable Request Item (asal).
     * Berguna untuk melacak: "Item PR ini asalnya dari Consumable Request mana?"
     */
    public function consumableRequestItem()
    {
        return $this->belongsTo(ConsumableRequestItem::class, 'consumable_request_item_id');
    }

    /**
     * Relasi ke Department (untuk Distribusi Stok).
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}