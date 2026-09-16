<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'location_name',
        'building',
        'room',
        'description',
    ];

    public static function resolveId(?string $name): ?int
    {
        $normalized = trim((string) $name);

        if ($normalized === '') {
            return null;
        }

        return (int) static::query()->firstOrCreate([
            'location_name' => $normalized,
        ], [
            'building' => null,
            'room' => null,
            'description' => null,
        ])->id;
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
