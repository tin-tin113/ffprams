<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Beneficiary extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'barangay_id',
        'classification',
        'contact_number',
        'household_size',
        'id_type',
        'government_id',
        'status',
        'registered_at',

        // Farmer-Specific (DA RSBSA)
        'rsbsa_number',
        'farm_ownership',
        'farm_size_hectares',
        'primary_commodity',
        'farm_type',

        // Fisherfolk-Specific (BFAR FishR)
        'fishr_number',
        'fisherfolk_type',
        'main_fishing_gear',
        'has_fishing_vessel',

        // Common
        'civil_status',
        'highest_education',
        'number_of_dependents',
        'main_income_source',
        'emergency_contact_name',
        'emergency_contact_number',
        'association_member',
        'association_name',
    ];

    protected function casts(): array
    {
        return [
            'registered_at'         => 'date',
            'has_fishing_vessel'    => 'boolean',
            'association_member'    => 'boolean',
            'number_of_dependents'  => 'integer',
            'farm_size_hectares'    => 'decimal:2',
        ];
    }

    // ── Relationships ─────────────────────────────

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    public function fieldAssessments(): HasMany
    {
        return $this->hasMany(FieldAssessment::class);
    }

    // ── Helpers ───────────────────────────────────

    public function hasApprovedAssessment(): bool
    {
        return $this->fieldAssessments()->where('approval_status', 'approved')->exists();
    }

    public function isFarmer(): bool
    {
        return in_array($this->classification, ['Farmer', 'Both'], true);
    }

    public function isFisherfolk(): bool
    {
        return in_array($this->classification, ['Fisherfolk', 'Both'], true);
    }
}
