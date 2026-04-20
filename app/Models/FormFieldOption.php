<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FormFieldOption extends Model
{
    public const FIELD_TYPE_TEXT = 'text';
    public const FIELD_TYPE_TEXTAREA = 'textarea';
    public const FIELD_TYPE_NUMBER = 'number';
    public const FIELD_TYPE_DECIMAL = 'decimal';
    public const FIELD_TYPE_DATE = 'date';
    public const FIELD_TYPE_DATETIME = 'datetime';
    public const FIELD_TYPE_DROPDOWN = 'dropdown';
    public const FIELD_TYPE_RADIO = 'radio';
    public const FIELD_TYPE_CHECKBOX = 'checkbox';

    public const PLACEMENT_PERSONAL_INFORMATION = 'personal_information';
    public const PLACEMENT_FARMER_INFORMATION = 'farmer_information';
    public const PLACEMENT_FISHERFOLK_INFORMATION = 'fisherfolk_information';
    public const PLACEMENT_DAR_INFORMATION = 'dar_information';

    protected $fillable = [
        'field_group',
        'field_type',
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

    public static function fieldTypeLabels(): array
    {
        return [
            self::FIELD_TYPE_TEXT => 'Text',
            self::FIELD_TYPE_TEXTAREA => 'Textarea',
            self::FIELD_TYPE_NUMBER => 'Number',
            self::FIELD_TYPE_DECIMAL => 'Decimal',
            self::FIELD_TYPE_DATE => 'Date',
            self::FIELD_TYPE_DATETIME => 'Date & Time',
            self::FIELD_TYPE_DROPDOWN => 'Dropdown',
            self::FIELD_TYPE_RADIO => 'Radio',
            self::FIELD_TYPE_CHECKBOX => 'Checkboxes',
        ];
    }

    public static function fieldTypeLabel(?string $fieldType): string
    {
        $normalizedType = strtolower(trim((string) $fieldType));
        $labels = self::fieldTypeLabels();

        return $labels[$normalizedType] ?? ucfirst(str_replace('_', ' ', $normalizedType));
    }

    public static function supportedFieldTypes(): array
    {
        return array_keys(self::fieldTypeLabels());
    }

    public static function optionBasedFieldTypes(): array
    {
        return [
            self::FIELD_TYPE_DROPDOWN,
            self::FIELD_TYPE_RADIO,
            self::FIELD_TYPE_CHECKBOX,
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
