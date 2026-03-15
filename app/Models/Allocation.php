<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Allocation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'distribution_event_id',
        'beneficiary_id',
        'quantity',
        'distributed_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'quantity'       => 'decimal:2',
            'distributed_at' => 'datetime',
        ];
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function distributionEvent(): BelongsTo
    {
        return $this->belongsTo(DistributionEvent::class);
    }
}
