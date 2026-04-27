<?php

namespace App\Models;

use App\Support\PhilippineMobileNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Beneficiary extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (self $beneficiary): void {
            $computedFullName = $beneficiary->buildFullName();

            if ($computedFullName !== '') {
                $beneficiary->full_name = $computedFullName;
            }
        });
    }

    protected $fillable = [
        'agency_id',
        'first_name',
        'middle_name',
        'last_name',
        'name_suffix',
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
        'highest_education',
        'id_type',
        'id_number',
        'association_member',
        'association_name',

        // DA/RSBSA fields
        'rsbsa_number',
        'farm_ownership',
        'farm_size_hectares',
        'primary_commodity',
        'farm_type',
        'organization_membership',
        'rsbsa_unavailability_reason',

        // BFAR/FishR fields
        'fishr_number',
        'fisherfolk_type',
        'main_fishing_gear',
        'has_fishing_vessel',
        'fishing_vessel_type',
        'fishing_vessel_tonnage',
        'length_of_residency_months',
        'fishr_unavailability_reason',

        // Custom fields
        'custom_fields',
        'custom_field_unavailability_reasons',
    ];

    protected function casts(): array
    {
        return [
            'registered_at'                      => 'date',
            'date_of_birth'                      => 'date',
            'has_fishing_vessel'                 => 'boolean',
            'association_member'                 => 'boolean',
            'farm_size_hectares'                 => 'decimal:2',
            'fishing_vessel_tonnage'             => 'decimal:2',
            'length_of_residency_months'         => 'integer',
            'custom_fields'                      => 'array',
            'custom_field_unavailability_reasons' => 'array',
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

    public function attachments(): HasMany
    {
        return $this->hasMany(BeneficiaryAttachment::class);
    }

    public function agencies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Agency::class, 'beneficiary_agencies')
            ->withPivot('identifier', 'registered_at')
            ->withTimestamps();
    }

    // ── Helpers ───────────────────────────────────

    public function isFarmer(): bool
    {
        return $this->classification === 'Farmer' || $this->classification === 'Farmer & Fisherfolk';
    }

    public function isFisherfolk(): bool
    {
        return $this->classification === 'Fisherfolk' || $this->classification === 'Farmer & Fisherfolk';
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

    public function getFullNameAttribute(?string $value): string
    {
        if (! empty($value)) {
            return $value;
        }

        return $this->buildFullName();
    }

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    public function setContactNumberAttribute(?string $value): void
    {
        if ($value === null) {
            $this->attributes['contact_number'] = null;

            return;
        }

        $trimmedValue = trim($value);

        if ($trimmedValue === '') {
            $this->attributes['contact_number'] = '';

            return;
        }

        $this->attributes['contact_number'] = PhilippineMobileNumber::normalize($trimmedValue) ?? $trimmedValue;
    }

    public function buildFullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->name_suffix,
        ])));
    }
}
