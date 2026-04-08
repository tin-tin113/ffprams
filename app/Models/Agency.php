<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    protected $fillable = [
        'name',
        'full_name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ─────────────────────────────

    public function resourceTypes(): HasMany
    {
        return $this->hasMany(ResourceType::class);
    }

    public function programNames(): HasMany
    {
        return $this->hasMany(ProgramName::class);
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(Beneficiary::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ── Scopes ────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCore(Builder $query): Builder
    {
        // Core FFPRAMS partner agencies used in geo-map filters.
        return $query->whereIn('name', ['DA', 'BFAR', 'DAR']);
    }
}
