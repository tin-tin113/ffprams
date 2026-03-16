<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FormFieldOption extends Model
{
    protected $fillable = [
        'field_group',
        'label',
        'value',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ];
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
