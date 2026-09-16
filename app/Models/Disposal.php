<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disposal extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'property_id',
        'assignment_id',
        'disposed_by',
        'admin_id',
        'processed_by',
        'quantity_disposed',
        'disposal_date',
        'disposal_reason',
        'disposal_method',
        'disposal_method_id',
        'remarks',
        'response_notes',
        'processed_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity_disposed' => 'integer',
            'disposal_date' => 'date',
            'processed_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function finalizedStatuses(): array
    {
        return [
            self::STATUS_APPROVED,
            self::STATUS_COMPLETED,
        ];
    }

    public static function decisionStatuses(): array
    {
        return [
            self::STATUS_APPROVED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function methods(): array
    {
        return [
            'auction',
            'donation',
            'recycling',
            'transfer',
            'condemnation',
            'destruction',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function disposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disposed_by');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function adminProfile(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function methodRecord(): BelongsTo
    {
        return $this->belongsTo(DisposalMethod::class, 'disposal_method_id');
    }
}
