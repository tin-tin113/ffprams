<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgencyFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'field_name',
        'display_label',
        'field_type',
        'is_required',
        'is_active',
        'sort_order',
        'help_text',
        'validation_rules',
        'form_section',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'validation_rules' => 'array',
    ];

    /**
     * Get the agency this field belongs to
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get all options for this field (for dropdown/checkbox types)
     */
    public function options(): HasMany
    {
        return $this->hasMany(AgencyFormFieldOption::class)
            ->orderBy('sort_order');
    }

    /**
     * Scope to get only active fields
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get fields for a specific form section
     */
    public function scopeForSection($query, $section)
    {
        return $query->where('form_section', $section);
    }

    /**
     * Scope to get fields sorted by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('display_label');
    }
}
