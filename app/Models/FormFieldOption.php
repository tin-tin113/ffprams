<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FormFieldOption extends Model
{
    public const PLACEMENT_PERSONAL_INFORMATION = 'personal_information';
    public const PLACEMENT_FARMER_INFORMATION = 'farmer_information';
    public const PLACEMENT_FISHERFOLK_INFORMATION = 'fisherfolk_information';
    public const PLACEMENT_DAR_INFORMATION = 'dar_information';

    protected $fillable = [
        'field_group',
        'placement_section',
        'label',
        'value',
        'sort_order',
        'is_required',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_required' => 'boolean',
            'is_active'  => 'boolean',
        ];
    }

    public static function placementLabels(): array
    {
        return [
            self::PLACEMENT_PERSONAL_INFORMATION => 'Agency & Personal Information',
            self::PLACEMENT_FARMER_INFORMATION => 'DA/RSBSA Information (Farmer)',
            self::PLACEMENT_FISHERFOLK_INFORMATION => 'BFAR/FishR Information (Fisherfolk)',
            self::PLACEMENT_DAR_INFORMATION => 'DAR/ARB Information',
        ];
    }

    public static function allowedPlacements(): array
    {
        return array_keys(self::placementLabels());
    }

    // ── Scopes ──────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForGroup($query, string $fieldGroup)
    {
        return $query->where('field_group', $fieldGroup);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    // ── Static Helper ───────────────────────────

    /**
     * Get active options for a given field group, ordered by sort_order.
     */
    public static function optionsFor(string $fieldGroup): Collection
    {
        return static::active()
            ->forGroup($fieldGroup)
            ->ordered()
            ->get();
    }
}
