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

    public const COMPLIANCE_STATUS_PROVIDED = 'provided';

    public const COMPLIANCE_STATUS_NOT_AVAILABLE_YET = 'not_available_yet';

    public const COMPLIANCE_STATUS_NOT_APPLICABLE = 'not_applicable';

    public const COMPLIANCE_STATUS_TO_BE_VERIFIED = 'to_be_verified';

    public const COMPLIANCE_TRACKED_FIELDS = [
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
        'farmc_reference_no',
        'farmc_endorsed_at',
    ];

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
        'compliance_field_states',
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
            'compliance_field_states' => 'array',
        ];
    }

    public static function complianceStatuses(): array
    {
        return [
            self::COMPLIANCE_STATUS_PROVIDED,
            self::COMPLIANCE_STATUS_NOT_AVAILABLE_YET,
            self::COMPLIANCE_STATUS_NOT_APPLICABLE,
            self::COMPLIANCE_STATUS_TO_BE_VERIFIED,
        ];
    }

    public function complianceStateFor(string $field): array
    {
        $savedStates = is_array($this->compliance_field_states)
            ? $this->compliance_field_states
            : [];

        $saved = $savedStates[$field] ?? [];
        $status = (string) ($saved['status'] ?? '');
        $reason = isset($saved['reason']) ? trim((string) $saved['reason']) : null;

        if (! in_array($status, self::complianceStatuses(), true)) {
            $status = $this->hasComplianceFieldValue($field)
                ? self::COMPLIANCE_STATUS_PROVIDED
                : self::COMPLIANCE_STATUS_NOT_AVAILABLE_YET;
        }

        if ($status === self::COMPLIANCE_STATUS_PROVIDED) {
            $reason = null;
        }

        return [
            'status' => $status,
            'reason' => $reason,
        ];
    }

    public function complianceStates(): array
    {
        $states = [];

        foreach (self::COMPLIANCE_TRACKED_FIELDS as $field) {
            $states[$field] = $this->complianceStateFor($field);
        }

        return $states;
    }

    private function hasComplianceFieldValue(string $field): bool
    {
        $value = $this->getAttribute($field);

        if (is_bool($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return $value !== null;
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

    public function unmarkedAllocationsCount(): int
    {
        return $this->allocations()
            ->where(function ($query) {
                $query->whereNull('release_outcome')
                    ->orWhereNotIn('release_outcome', ['received', 'not_received']);
            })
            ->count();
    }

    public function hasAllBeneficiariesMarked(): bool
    {
        return $this->unmarkedAllocationsCount() === 0;
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
