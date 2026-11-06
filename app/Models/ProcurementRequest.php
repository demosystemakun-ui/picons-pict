<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REVISION = 'revision';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'no',
        'date',
        'department_id',
        'request_by',
        'division',
        'prepared_by',
        'description',
        'source',

        'is_new_order',
        'is_repeat_order',
        'is_goods',
        'is_services',

        'background',
        'purpose',
        'required_spec',

        'item_name',
        'item_requirement_note',
        'quantity',
        'target_purchase',

        'pic_name',
        'ops_manager_name',
        'fem_manager_name',
        'coo_name',

         'is_pic_approved',
        'is_ops_approved',
        'is_fem_approved',
        'is_coo_approved',

        'status',
         'procurement_method',
        'review_notes',

        'budget_type',
        'reason_choose_vendor',
        'final_total_price',
        'accounting_mgr_name',
        'ceo_name',
        'cfo_name',

        'user_id',
    ];

    protected $casts = [
        'date'              => 'datetime',
        'is_new_order'      => 'boolean',
        'is_repeat_order'   => 'boolean',
        'is_goods'          => 'boolean',
        'is_services'       => 'boolean',
        'final_total_price' => 'decimal:2',
    ];

    /* ═══════════════════════════════════════════════════════════════
       RELATIONS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Item-item detail PR.
     */
    public function items()
    {
        return $this->hasMany(ProcurementRequestItem::class);
    }

    /**
     * Semua vendor comparison untuk PR ini (urut dari termurah).
     * Menggunakan tabel procurement_vendors (sistem baru).
     */
    public function vendors()
    {
        return $this->hasMany(ProcurementVendor::class)->orderBy('total_price');
    }

    /**
     * Vendor pemenang (harga terendah / is_winner = true).
     */
    public function winnerVendor()
    {
        return $this->hasOne(ProcurementVendor::class)->where('is_winner', true);
    }

    /**
     * User yang membuat PR.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Departemen pemohon.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Dokumen arsip (jika ada).
     */
    public function documents()
    {
        return $this->morphMany(DocumentArchive::class, 'documentable');
    }

    /* ═══════════════════════════════════════════════════════════════
       BOOT — Auto-normalize order type (hanya 1 yang boleh true)
    ═══════════════════════════════════════════════════════════════ */
    protected static function booted()
    {
        static::saving(function ($model) {
            $activeType = collect([
                'is_new_order'    => $model->is_new_order,
                'is_repeat_order' => $model->is_repeat_order,
                'is_goods'        => $model->is_goods,
                'is_services'     => $model->is_services,
            ])->search(fn ($value) => $value === true || $value === 1 || $value === '1');

            $model->is_new_order    = false;
            $model->is_repeat_order = false;
            $model->is_goods        = false;
            $model->is_services     = false;

            if ($activeType) {
                $model->{$activeType} = true;
            }
        });
    }

    /* ═══════════════════════════════════════════════════════════════
       HELPERS
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Generate nomor PR otomatis.
     * Format: /PR/{DIVISION}/{PREFIX}-{ROMAN_MONTH}-{YEAR}
     * Contoh: /PR/OPS/PICT-IX-2026
     */
    public static function generateNumber(string $division = 'OPS', string $prefix = 'PICT'): string
    {
        $romans = [
            1  => 'I',   2  => 'II',  3  => 'III', 4  => 'IV',
            5  => 'V',   6  => 'VI',  7  => 'VII', 8  => 'VIII',
            9  => 'IX',  10 => 'X',   11 => 'XI',  12 => 'XII',
        ];

        $currentMonth = (int) now()->format('n');
        $monthRoman   = $romans[$currentMonth] ?? 'I';
        $year         = now()->format('Y');

        return "/PR/{$division}/{$prefix}-{$monthRoman}-{$year}";
    }

    /* ═══════════════════════════════════════════════════════════════
       SCOPES (Opsional)
    ═══════════════════════════════════════════════════════════════ */

    /**
     * Scope: PR dengan status Pending.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: PR yang sudah disetujui dan siap diproses.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope: PR milik departemen tertentu.
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
}