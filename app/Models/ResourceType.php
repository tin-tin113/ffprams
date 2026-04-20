<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceType extends Model
{
    public const UNIT_OPTIONS = [
        'PHP' => 'PHP (Pesos)',
        'kg' => 'Kilogram (kg)',
        'g' => 'Gram (g)',
        'liters' => 'Liters',
        'ml' => 'Milliliters (ml)',
        'sacks' => 'Sacks',
        'boxes' => 'Boxes',
        'packs' => 'Packs',
        'pieces' => 'Pieces',
        'sets' => 'Sets',
    ];

    protected $fillable = [
        'name',
        'unit',
        'description',
        'agency_id',
        'is_active',
    ];

    public static function unitOptions(): array
    {
        return self::UNIT_OPTIONS;
    }

    public static function unitValues(): array
    {
        return array_keys(self::UNIT_OPTIONS);
    }

    public static function normalizeUnit(?string $unit): string
    {
        $normalized = trim((string) $unit);
        if ($normalized === '') {
            return '';
        }

        $lowered = strtolower($normalized);
        if (in_array($lowered, ['php', 'peso', 'pesos', 'philippine peso', 'philippine pesos', '₱'], true)) {
            return 'PHP';
        }

        return $normalized;
    }

    public static function isFinancialUnit(?string $unit): bool
    {
        return self::normalizeUnit($unit) === 'PHP';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function distributionEvents(): HasMany
    {
        return $this->hasMany(DistributionEvent::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
