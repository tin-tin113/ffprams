<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceType extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'description',
        'source_agency',
    ];

    public function distributionEvents(): HasMany
    {
        return $this->hasMany(DistributionEvent::class);
    }
}
