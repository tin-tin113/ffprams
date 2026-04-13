<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionEvent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'barangay_id',
        'resource_type_id',
        'program_name_id',
        'distribution_date',
        'status',
        'beneficiary_list_approved_at',
        'beneficiary_list_approved_by',
        'created_by',
        'type',
        'total_fund_amount',
        'legal_basis_type',
        'legal_basis_reference_no',
        'legal_basis_date',
        'legal_basis_remarks',
        'fund_source',
        'trust_account_code',
        'fund_release_reference',
        'liquidation_status',
        'liquidation_due_date',
        'liquidation_submitted_at',
        'liquidation_reference_no',
        'requires_farmc_endorsement',
        'farmc_endorsed_at',
        'farmc_reference_no',
    ];

    protected function casts(): array
    {
        return [
            'distribution_date'  => 'date',
            'total_fund_amount'  => 'decimal:2',
            'beneficiary_list_approved_at' => 'datetime',
            'legal_basis_date' => 'date',
            'liquidation_due_date' => 'date',
            'liquidation_submitted_at' => 'datetime',
            'requires_farmc_endorsement' => 'boolean',
            'farmc_endorsed_at' => 'datetime',
        ];
    }

    public function isBeneficiaryListApproved(): bool
    {
        return $this->beneficiary_list_approved_at !== null;
    }

    public function isFinancial(): bool
    {
        return $this->type === 'financial';
    }

    public function hasLegalBasis(): bool
    {
        return filled($this->legal_basis_type)
            && filled($this->legal_basis_reference_no)
            && $this->legal_basis_date !== null;
    }

    public function hasFundSource(): bool
    {
        return filled($this->fund_source);
    }

    public function isLiquidationVerified(): bool
    {
        return $this->liquidation_status === 'verified';
    }

    public function isFarmcCompliant(): bool
    {
        return ! $this->requires_farmc_endorsement || $this->farmc_endorsed_at !== null;
    }

    public function isPhysical(): bool
    {
        return $this->type === 'physical';
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function programName(): BelongsTo
    {
        return $this->belongsTo(ProgramName::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function beneficiaryListApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'beneficiary_list_approved_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(RecordAttachment::class, 'attachable');
    }
}
