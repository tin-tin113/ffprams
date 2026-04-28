<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Allocation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'distribution_event_id',
        'release_method',
        'beneficiary_id',
        'program_name_id',
        'resource_type_id',
        'quantity',
        'amount',
        'is_ready_for_release',
        'distributed_at',
        'release_outcome',
        'remarks',
        'assistance_purpose_id',
        'legacy_id',
        'legacy_source',
        'created_by',
        'distributed_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity'       => 'decimal:2',
            'amount'         => 'decimal:2',
            'is_ready_for_release' => 'boolean',
            'distributed_at' => 'datetime',
            'release_outcome' => 'string',
        ];
    }

    public function scopeWhereReleaseStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            'released' => $query->where(function (Builder $releasedQuery) {
                $releasedQuery->whereNotNull('distributed_at')
                    ->orWhere('release_outcome', 'received');
            }),
            'not_received' => $query->where('release_outcome', 'not_received'),
            'ready_for_release' => $query
                ->where('is_ready_for_release', true)
                ->whereNull('distributed_at'),
            'planned' => $query
                ->whereNull('distributed_at')
                ->whereNull('release_outcome')
                ->where(function (Builder $plannedQuery) {
                    $plannedQuery->whereNull('is_ready_for_release')
                        ->orWhere('is_ready_for_release', false);
                }),
            default => $query,
        };
    }

    public function getReleaseStatusAttribute(): string
    {
        if ($this->distributed_at !== null || $this->release_outcome === 'received') {
            return 'released';
        }

        if ($this->release_outcome === 'not_received') {
            return 'not_received';
        }

        if ((bool) $this->is_ready_for_release) {
            return 'ready_for_release';
        }

        return 'planned';
    }

    public function getReleaseStatusLabelAttribute(): string
    {
        return match ($this->release_status) {
            'ready_for_release' => 'Ready for Release',
            'released' => 'Released',
            'not_received' => 'Not Received',
            default => 'Planned',
        };
    }

    public function getDisplayValue(): string
    {
        $isFinancial = false;

        if ($this->distributionEvent) {
            $isFinancial = $this->distributionEvent->isFinancial();
        } elseif (($this->resourceType?->unit ?? null) === 'PHP') {
            $isFinancial = true;
        }

        if ($isFinancial) {
            return '₱' . number_format((float) $this->amount, 2);
        }

        $unit = $this->distributionEvent?->resourceType?->unit
            ?? $this->resourceType?->unit
            ?? '';

        return number_format((float) $this->quantity, 2) . ' ' . $unit;
    }

    public function isDirect(): bool
    {
        return $this->release_method === 'direct';
    }

    public function isEventBased(): bool
    {
        return $this->release_method === 'event';
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function distributionEvent(): BelongsTo
    {
        return $this->belongsTo(DistributionEvent::class);
    }

    public function programName(): BelongsTo
    {
        return $this->belongsTo(ProgramName::class);
    }

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function assistancePurpose(): BelongsTo
    {
        return $this->belongsTo(AssistancePurpose::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function distributedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(RecordAttachment::class, 'attachable');
    }
}
