<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssistancePurpose extends Model
{
    protected $fillable = [
        'name',
        'category',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ─────────────────────────────

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }

    // ── Scopes ────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    // ── Category & Type Options ────────────────────

    /**
     * Get all available category options with their subcategory types
     */
    public static function getCategoryOptions(): array
    {
        return [
            'production' => [
                'label' => 'Production',
                'types' => [
                    'Seeds & Seedlings',
                    'Fertilizers & Soil Amendments',
                    'Farm Equipment & Tools',
                    'Pesticides & Farm Inputs',
                    'Irrigation System',
                    'Livestock Assistance',
                    'Fishery/Aquaculture',
                    'Production Infrastructure',
                ]
            ],
            'livelihood' => [
                'label' => 'Livelihood',
                'types' => [
                    'Skills Training',
                    'Alternative Income Program',
                    'Business Capital',
                    'Market Access Support',
                    'Post-Harvest Processing',
                    'Cooperative Development',
                    'Value-Chain Development',
                ]
            ],
            'emergency' => [
                'label' => 'Emergency',
                'types' => [
                    'Disaster Relief',
                    'Emergency Food Assistance',
                    'Medical/Health Assistance',
                    'Livelihood Recovery',
                    'Infrastructure Rehabilitation',
                ]
            ]
        ];
    }

    /**
     * Get types for a specific category
     */
    public static function getTypesByCategory(string $category): array
    {
        $options = self::getCategoryOptions();
        return $options[$category]['types'] ?? [];
    }
}
