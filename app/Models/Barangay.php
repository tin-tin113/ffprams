<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barangay extends Model
{
    protected $fillable = [
        'name',
        'municipality',
        'province',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(Beneficiary::class);
    }

    /**
     * Scope to filter barangays to E.B. Magalona only.
     * This ensures the geo-map and beneficiary data are exclusive to this municipality.
     */
    public function scopeEbMagalona($query)
    {
        return $query->where('municipality', '=', 'E.B. Magalona')
            ->where('province', '=', 'Negros Occidental');
    }
}
