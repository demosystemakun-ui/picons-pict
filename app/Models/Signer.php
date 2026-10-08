<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signer extends Model
{
    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public const SLOTS = [
        'checked'     => 'Checked',
        'acknowledge' => 'Acknowledge',
        'verified'    => 'Verified',
        'approved'    => 'Approved',
     
    ];
}