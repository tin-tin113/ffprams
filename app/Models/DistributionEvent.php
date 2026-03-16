<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionEvent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'barangay_id',
        'resource_type_id',
        'distribution_date',
        'status',
        'created_by',
        'type',
        'total_fund_amount',
    ];

    protected function casts(): array
    {
        return [
            'distribution_date'  => 'date',
            'total_fund_amount'  => 'decimal:2',
        ];
    }

    public function isFinancial(): bool
    {
        return $this->type === 'financial';
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }
}
