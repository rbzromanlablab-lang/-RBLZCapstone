<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PropertyUnit extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_DISPOSED = 'disposed';

    protected $fillable = [
        'property_id',
        'assignment_id',
        'serial_number',
        'qr_token',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (PropertyUnit $propertyUnit): void {
            if (! $propertyUnit->qr_token) {
                $propertyUnit->qr_token = self::generateQrToken();
            }
        });
    }

    public static function generateQrToken(): string
    {
        do {
            $token = (string) Str::uuid();
        } while (self::query()->where('qr_token', $token)->exists());

        return $token;
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }
}
