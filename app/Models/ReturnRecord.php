<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ReturnRecord extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'returns';

    protected $fillable = [
        'assignment_id',
        'returned_by',
        'return_date',
        'status',
        'returned_serial_numbers',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}
