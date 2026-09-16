<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisposalMethod extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'method_name',
        'description',
    ];

    public static function resolveId(?string $name): ?int
    {
        $normalized = trim((string) $name);

        if ($normalized === '') {
            return null;
        }

        return (int) static::query()->firstOrCreate([
            'method_name' => $normalized,
        ], [
            'description' => null,
        ])->id;
    }

    public function disposals(): HasMany
    {
        return $this->hasMany(Disposal::class);
    }
}
