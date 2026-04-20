<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgencyFormFieldOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_form_field_id',
        'label',
        'value',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the form field this option belongs to
     */
    public function formField(): BelongsTo
    {
        return $this->belongsTo(AgencyFormField::class, 'agency_form_field_id');
    }

    /**
     * Scope to get only active options
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
