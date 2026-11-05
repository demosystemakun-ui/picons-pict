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
        'revision_note',
        'revision_requested_at',
        'pr_generated_at',
    ];

    protected $casts = [
        'date'                  => 'date',
        'revision_requested_at' => 'datetime',
        'pr_generated_at'       => 'datetime',
    ];

    /* ═══════════════════════════════════════════════════════════════
       RELATIONS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Item-item detail Consumable Request.
     */
    public function items()
    {
        return $this->hasMany(ConsumableRequestItem::class);
    }

    /**
     * User (PIC) yang membuat request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Procurement Request (PR) yang dihasilkan.
     * Bisa null jika belum dikonsolidasi.
     * (Menggunakan hasOne karena 1 Consumable biasanya hanya jadi 1 item di PR, 
     * tapi jika ingin many-to-many, bisa disesuaikan).
     */
    public function procurementRequest()
    {
        return $this->hasOne(ProcurementRequest::class, 'source_id');
    }

    /* ═══════════════════════════════════════════════════════════════
       HELPERS & SCOPES
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Apakah request sedang dalam status revisi?
     */
    public function isOnRevision(): bool
    {
        return $this->status === 'revision';
    }

    /**
     * Scope: Hanya ambil request yang statusnya Approved.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Hanya ambil request yang sudah dikonversi ke PR.
     */
    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    /**
     * Scope: Hanya ambil request milik departemen tertentu.
     * Digunakan untuk memfilter data berdasarkan Admin Departemen.
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->whereHas('user', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }
}