<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentArchive extends Model
{
    protected $fillable = [
        'documentable_id',
        'documentable_type',
        'file_path',
        'file_name',
        'file_type',
        'category',
        'uploaded_by',
    ];

    public function documentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}