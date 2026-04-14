<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramLegalRequirement extends Model
{
    protected $fillable = [
        'program_name_id',
        'uploaded_by',
        'document_type',
        'original_name',
        'stored_name',
        'path',
        'disk',
        'mime_type',
        'extension',
        'size_bytes',
        'sha256',
        'remarks',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(ProgramName::class, 'program_name_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
