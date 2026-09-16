<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_TRANSFERRED = 'transferred';
    public const STATUS_DISPOSED = 'disposed';

    protected $fillable = [
        'property_id',
        'teacher_id',
        'teacher_profile_id',
        'assigned_by',
        'staff_id',
        'quantity_assigned',
        'date_assigned',
        'expected_return_date',
        'location',
        'location_id',
        'assigned_at',
        'returned_at',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity_assigned' => 'integer',
            'date_assigned' => 'date',
            'expected_return_date' => 'date',
            'assigned_at' => 'date',
            'returned_at' => 'date',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_profile_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function assignmentLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function returnRecords(): HasMany
    {
        return $this->hasMany(ReturnRecord::class, 'assignment_id');
    }

    public function propertyUnits(): HasMany
    {
        return $this->hasMany(PropertyUnit::class)
            ->where('status', PropertyUnit::STATUS_ASSIGNED)
            ->orderBy('id');
    }
}
