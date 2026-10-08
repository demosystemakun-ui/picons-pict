<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumableRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'consumable_request_id',
        'item_name',
        'brand',
        'type',
        'model',
        'capacity',
        'specs',
        'item_details',   // legacy, tetap ada untuk backward-compat
        'purpose_1',
        'purpose_2',
        'purpose_3',
        'quantity',
        'unit',
        'picture',
    ];

    public function consumableRequest()
    {
        return $this->belongsTo(ConsumableRequest::class);
    }

    /**
     * Helper: gabungkan semua field detail menjadi 1 string
     * untuk ditampilkan di print/PDF.
     */
    public function getFormattedDetailsAttribute(): string
    {
        $lines = [];

        if ($this->item_name) $lines[] = 'Item: ' . $this->item_name;
        if ($this->brand)     $lines[] = 'Brand: ' . $this->brand;
        if ($this->type)      $lines[] = 'Type: ' . $this->type;
        if ($this->model)     $lines[] = 'Model: ' . $this->model;
        if ($this->capacity)  $lines[] = 'Capacity: ' . $this->capacity;
        if ($this->specs)     $lines[] = $this->specs;

        // Fallback ke item_details lama kalau field baru kosong
        if (empty($lines) && $this->item_details) {
            return $this->item_details;
        }

        return implode("\n", $lines);
    }
}