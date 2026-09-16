<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyCategory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'category_name',
        'description',
    ];

    public static function resolveId(?string $name): ?int
    {
        $normalized = trim((string) $name);

        if ($normalized === '') {
            return null;
        }

        return (int) static::query()->firstOrCreate([
            'category_name' => $normalized,
        ], [
            'description' => null,
        ])->id;
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
