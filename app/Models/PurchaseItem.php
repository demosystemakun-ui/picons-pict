<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id', 
        'product_name', 
        'qty_ordered', 
        'qty_received', 
        'qty_damaged', 
        'unit_price'
    ];

    protected $casts = [
        'qty_ordered'  => 'integer',
        'qty_received' => 'integer',
        'qty_damaged'  => 'integer',
        'unit_price'   => 'decimal:2',
    ];

    /* ═══════════════════════════════════════════════════════════════
       RELATIONS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Relasi ke Purchase (Induk).
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /* ═══════════════════════════════════════════════════════════════
       ACCESSORS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Sisa Qty yang belum diterima.
     * Pemakaian di Blade: {{ $item->remaining_qty }}
     */
    public function getRemainingQtyAttribute(): int
    {
        return max(0, $this->qty_ordered - $this->qty_received);
    }

    /**
     * Apakah item ini sudah diterima lengkap?
     * Pemakaian di Blade: @if($item->is_fully_received)
     */
    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->qty_received >= $this->qty_ordered;
    }

    /**
     * Subtotal harga item.
     * Pemakaian di Blade: {{ $item->subtotal_formatted }}
     */
    public function getSubtotalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->qty_ordered * $this->unit_price, 0, ',', '.');
    }
}