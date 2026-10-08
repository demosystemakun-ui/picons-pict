<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumableRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'subject',
        'general_note',
        'request_by',
        'approved_by',
        'verified_by',
        'status',
        'pr_generated_at',
    ];

    protected $casts = [
        'date' => 'date',
        'pr_generated_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(ConsumableRequestItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}