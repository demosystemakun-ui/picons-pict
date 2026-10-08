<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementRequest extends Model
{
    use HasFactory;

    /* ═══════════════════════════════════════════════════════════════
       MASS ASSIGNMENT
    ═══════════════════════════════════════════════════════════════ */
    protected $fillable = [
        'no',
        'date',
        'department_id',
        'request_by',
        'division',
        'prepared_by',
        'description',

        // Order type (boolean)
        'is_new_order',
        'is_repeat_order',
        'is_goods',
        'is_services',

        // Detail kebutuhan
        'background',
        'purpose',
        'required_spec',

        // Item ringkasan (legacy)
        'item_name',
        'item_requirement_note',
        'quantity',
        'target_purchase',

        // Approval chain
        'pic_name',
        'ops_manager_name',
        'fem_manager_name',
        'coo_name',

        // Status
        'status',
        'review_notes',

        // ═══ Comparison & Final Decision ═══
        'budget_type',
        'reason_choose_vendor',
        'final_vendor_id',
        'final_total_price',
        'accounting_mgr_name',
        'ceo_name',
        'cfo_name',

        // Relasi user
        'user_id',
    ];

    /* ═══════════════════════════════════════════════════════════════
       CASTS
    ═══════════════════════════════════════════════════════════════ */
    protected $casts = [
        'date'              => 'date',
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
     * Item-item detail PR (multiple items).
     */
    public function items()
    {
        return $this->hasMany(ProcurementRequestItem::class);
    }

    /**
     * Semua vendor comparison untuk PR ini (urut dari termurah).
     */
    public function vendors()
    {
        return $this->hasMany(ProcurementVendor::class)
            ->orderBy('total_price');
    }

    /**
     * Vendor pemenang (harga terendah).
     */
    public function winnerVendor()
    {
        return $this->hasOne(ProcurementVendor::class)
            ->where('is_winner', true);
    }

    /**
     * Comparison lama (legacy — bisa dihapus jika sudah migrasi penuh).
     */
    public function comparisons()
    {
        return $this->hasMany(ProcurementComparison::class)
            ->orderBy('sort_order');
    }

    /**
     * Selected comparison (legacy).
     */
    public function selectedComparison()
    {
        return $this->hasOne(ProcurementComparison::class)
            ->where('is_selected', true);
    }

    /**
     * Final vendor via FK (legacy).
     */
    public function finalVendor()
    {
        return $this->belongsTo(ProcurementComparison::class, 'final_vendor_id');
    }

    /**
     * Vendor comparison lama (legacy).
     */
    public function vendorComparisons()
    {
        return $this->hasMany(VendorComparison::class);
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

            // Reset semua
            $model->is_new_order    = false;
            $model->is_repeat_order = false;
            $model->is_goods        = false;
            $model->is_services     = false;

            // Set satu yang aktif
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
}