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
        'agency_id',
        'full_name',
        'sex',
        'date_of_birth',
        'photo_path',
        'home_address',
        'barangay_id',
        'classification',
        'contact_number',
        'status',
        'registered_at',
        'civil_status',
        'association_member',
        'association_name',

        // DA/RSBSA fields
        'rsbsa_number',
        'farm_ownership',
        'farm_size_hectares',
        'primary_commodity',
        'farm_type',
        'organization_membership',

        // BFAR/FishR fields
        'fishr_number',
        'fisherfolk_type',
        'main_fishing_gear',
        'has_fishing_vessel',
        'fishing_vessel_type',
        'fishing_vessel_tonnage',
        'length_of_residency_months',

        // DAR/ARB fields
        'cloa_ep_number',
        'arb_classification',
        'landholding_description',
        'land_area_awarded_hectares',
        'ownership_scheme',
        'barc_membership_status',
    ];

    protected function casts(): array
    {
        return [
            'registered_at'              => 'date',
            'date_of_birth'              => 'date',
            'has_fishing_vessel'         => 'boolean',
            'association_member'         => 'boolean',
            'farm_size_hectares'         => 'decimal:2',
            'fishing_vessel_tonnage'     => 'decimal:2',
            'land_area_awarded_hectares' => 'decimal:2',
            'length_of_residency_months' => 'integer',
        ];
    }

    // ── Relationships ─────────────────────────────

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

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

    // ── Helpers ───────────────────────────────────

    public function isFarmer(): bool
    {
        return in_array($this->classification, ['Farmer', 'Both'], true);
    }

    public function isFisherfolk(): bool
    {
        return in_array($this->classification, ['Fisherfolk', 'Both'], true);
    }

    public function isDar(): bool
    {
        return $this->agency && strtoupper($this->agency->name) === 'DAR';
    }

    public function isDa(): bool
    {
        return $this->agency && strtoupper($this->agency->name) === 'DA';
    }

    public function isBfar(): bool
    {
        return $this->agency && strtoupper($this->agency->name) === 'BFAR';
    }
}
