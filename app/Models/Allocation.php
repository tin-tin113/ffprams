<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'distributed_at',
        'release_outcome',
        'remarks',
        'assistance_purpose_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity'       => 'decimal:2',
            'amount'         => 'decimal:2',
            'distributed_at' => 'datetime',
            'release_outcome' => 'string',
        ];
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
}
