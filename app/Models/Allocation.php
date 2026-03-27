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
        'beneficiary_id',
        'quantity',
        'amount',
        'distributed_at',
        'remarks',
        'assistance_purpose_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity'       => 'decimal:2',
            'amount'         => 'decimal:2',
            'distributed_at' => 'datetime',
        ];
    }

    public function getDisplayValue(): string
    {
        if ($this->distributionEvent && $this->distributionEvent->isFinancial()) {
            return '₱' . number_format((float) $this->amount, 2);
        }

        $unit = $this->distributionEvent?->resourceType?->unit ?? '';

        return number_format((float) $this->quantity, 2) . ' ' . $unit;
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function distributionEvent(): BelongsTo
    {
        return $this->belongsTo(DistributionEvent::class);
    }

    public function assistancePurpose(): BelongsTo
    {
        return $this->belongsTo(AssistancePurpose::class);
    }
}
