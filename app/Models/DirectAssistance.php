<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DirectAssistance extends Model
{
    use SoftDeletes;

    private const LEGACY_STATUS_ALIASES = [
        'recorded' => 'planned',
        'distributed' => 'ready_for_release',
        'completed' => 'released',
    ];

    private const STATUS_LABELS = [
        'planned' => 'Planned',
        'ready_for_release' => 'Ready for Release',
        'released' => 'Released',
        'not_received' => 'Not Received',
    ];

    private const FILTER_STATUS_MAP = [
        'planned' => ['planned', 'recorded'],
        'ready_for_release' => ['ready_for_release', 'distributed'],
        'released' => ['released', 'completed'],
        'not_received' => ['not_received'],
    ];

    protected $table = 'direct_assistance';

    protected $fillable = [
        'beneficiary_id',
        'program_name_id',
        'resource_type_id',
        'assistance_purpose_id',
        'quantity',
        'amount',
        'distributed_at',
        'release_outcome',
        'remarks',
        'created_by',
        'distributed_by',
        'status',
        'distribution_event_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity'       => 'decimal:2',
            'amount'         => 'decimal:2',
            'distributed_at' => 'datetime',
            'status'         => 'string',
        ];
    }

    /**
     * Get display value (formatted quantity or amount with unit)
     */
    public function getDisplayValue(): string
    {
        $resourceType = $this->resourceType;

        if (!$resourceType) {
            return 'N/A';
        }

        if ($resourceType->unit === 'PHP' || $this->amount) {
            return '₱' . number_format((float) $this->amount, 2);
        }

        return number_format((float) $this->quantity, 2) . ' ' . $resourceType->unit;
    }

    /**
     * Check if assistance has been distributed to beneficiary
     */
    public function isDistributed(): bool
    {
        return $this->distributed_at !== null || $this->normalized_status === 'released';
    }

    public static function normalizeStatus(?string $status): string
    {
        $status = (string) ($status ?? '');

        if ($status === '') {
            return 'planned';
        }

        return self::LEGACY_STATUS_ALIASES[$status] ?? $status;
    }

    public static function statusesForFilter(string $status): array
    {
        $normalized = self::normalizeStatus($status);

        return self::FILTER_STATUS_MAP[$normalized] ?? [$normalized];
    }

    public function scopeWhereStatusNormalized(Builder $query, string $status): Builder
    {
        return $query->whereIn('status', self::statusesForFilter($status));
    }

    public function getNormalizedStatusAttribute(): string
    {
        return self::normalizeStatus($this->status);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->normalized_status] ?? Str::headline($this->normalized_status);
    }

    /**
     * Check if assistance is for financial aid
     */
    public function isFinancial(): bool
    {
        return $this->resourceType?->unit === 'PHP' || ($this->amount !== null && $this->quantity === null);
    }

    // ── Relationships ────────────────────────────────

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
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

    public function distributionEvent(): BelongsTo
    {
        return $this->belongsTo(DistributionEvent::class)->nullable();
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(RecordAttachment::class, 'attachable');
    }
}
