<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldAssessment extends Model
{
    protected $fillable = [
        'beneficiary_id',
        'assessed_by',
        'visit_date',
        'visit_time',
        'findings',
        'eligibility_status',
        'eligibility_notes',
        'recommended_assistance_purpose_id',
        'recommended_amount',
        'approved_by',
        'approved_at',
        'approval_status',
        'approval_notes',
    ];

    protected function casts(): array
    {
        return [
            'visit_date'          => 'date',
            'approved_at'         => 'datetime',
            'recommended_amount'  => 'decimal:2',
        ];
    }

    // ── Relationships ─────────────────────────────

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recommendedPurpose(): BelongsTo
    {
        return $this->belongsTo(AssistancePurpose::class, 'recommended_assistance_purpose_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }

    // ── Helpers ───────────────────────────────────

    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function isEligible(): bool
    {
        return $this->eligibility_status === 'eligible';
    }
}
