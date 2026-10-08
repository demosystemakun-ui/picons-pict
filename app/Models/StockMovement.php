<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockMovement extends Model
{
    protected $fillable = [
        'item_id', 'type', 'quantity', 'stock_before', 'stock_after',
        'department_id', 'reason', 'reference_type', 'reference_id', 'created_by',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Record a stock movement and update the item's cached current_stock
     * atomically. This is the single entry point all inbound/outbound/
     * adjustment flows should go through — never mutate items.current_stock
     * directly anywhere else in the app.
     *
     * @param  Item    $item
     * @param  string  $type       'in' | 'out' | 'adjustment'
     * @param  float   $quantity   positive number; direction is derived from $type
     * @param  array   $meta       ['department_id' => ?, 'reason' => ?, 'reference' => Model|null, 'created_by' => ?]
     */
    public static function record(Item $item, string $type, float $quantity, array $meta = []): self
    {
        return DB::transaction(function () use ($item, $type, $quantity, $meta) {
            // lock the row to avoid race conditions on concurrent movements
            $item = Item::whereKey($item->id)->lockForUpdate()->first();

            $signedQuantity = match ($type) {
                'in' => abs($quantity),
                'out' => -abs($quantity),
                'adjustment' => $quantity, // caller passes signed value directly
                default => throw new \InvalidArgumentException("Unknown movement type: {$type}"),
            };

            $before = $item->current_stock;
            $after = $before + $signedQuantity;

            if ($after < 0) {
                throw new \RuntimeException("Insufficient stock for item [{$item->item_name}]: available {$before}, requested " . abs($signedQuantity));
            }

            $movement = self::create([
                'item_id' => $item->id,
                'type' => $type,
                'quantity' => $signedQuantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'department_id' => $meta['department_id'] ?? null,
                'reason' => $meta['reason'] ?? null,
                'reference_type' => isset($meta['reference']) ? get_class($meta['reference']) : null,
                'reference_id' => $meta['reference']->id ?? null,
                'created_by' => $meta['created_by'] ?? auth()->id(),
            ]);

            $item->update(['current_stock' => $after]);

            return $movement;
        });
    }
}
