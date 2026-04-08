<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramName extends Model
{
    protected $fillable = [
        'agency_id',
        'name',
        'is_active',
        'classification',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'classification' => 'string',
        ];
    }

    // ── Relationships ─────────────────────────────

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function distributionEvents(): HasMany
    {
        return $this->hasMany(DistributionEvent::class);
    }

    // ── Scopes ────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForAgency(Builder $query, int $agencyId): Builder
    {
        return $query->where('agency_id', $agencyId);
    }

    public function scopeForClassification(Builder $query, string $classification): Builder
    {
        return $query->whereIn('classification', [$classification, 'Both']);
    }

    public function scopeForBeneficiary(Builder $query, $beneficiary): Builder
    {
        return $query->where('agency_id', $beneficiary->agency_id)
            ->whereIn('classification', [$beneficiary->classification, 'Both']);
    }
}
