<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProcurementComparisonAttachment extends Model
{
    protected $fillable = [
        'procurement_request_id',
        'procurement_vendor_id',
        'mode',
        'file_path',
        'disk',
        'original_name',
        'sort_order',
    ];

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ProcurementVendor::class, 'procurement_vendor_id');
    }

    public function getUrlAttribute(): string
    {
        if ($this->disk === 'public') {
            return asset('storage/' . $this->file_path);
        }

        try {
            return Storage::disk($this->disk)->url($this->file_path);
        } catch (\Throwable $e) {
            return asset('storage/' . $this->file_path);
        }
    }
}