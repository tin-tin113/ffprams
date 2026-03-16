<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceType extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'description',
        'source_agency',
        'agency_id',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (ResourceType $resourceType) {
            if ($resourceType->agency_id) {
                $agency = Agency::find($resourceType->agency_id);
                if ($agency) {
                    $resourceType->source_agency = $agency->name;
                }
            }
        });
    }

    public function distributionEvents(): HasMany
    {
        return $this->hasMany(DistributionEvent::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
