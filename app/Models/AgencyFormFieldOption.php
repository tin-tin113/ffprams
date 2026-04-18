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
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the form field this option belongs to
     */
    public function formField(): BelongsTo
    {
        return $this->belongsTo(AgencyFormField::class, 'agency_form_field_id');
    }
}
