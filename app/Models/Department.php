<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 
        'name', 
        'cost_center', 
        'is_active'
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /* ═══════════════════════════════════════════════════════════════
       RELATIONS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Relasi ke User (Staf/PIC/Admin Departemen).
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relasi ke Procurement Request (PR) yang dibuat oleh/untuk departemen ini.
     */
    public function procurementRequests()
    {
        return $this->hasMany(ProcurementRequest::class);
    }

    /**
     * Relasi ke Inventory Stock milik departemen ini.
     */
    public function inventoryStocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    /**
     * Relasi ke Item PR (jika tiap item PR mencatat department_id).
     */
    public function procurementRequestItems()
    {
        return $this->hasMany(ProcurementRequestItem::class);
    }

    /* ═══════════════════════════════════════════════════════════════
       SCOPES (Opsional)
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Scope: Hanya ambil departemen yang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}