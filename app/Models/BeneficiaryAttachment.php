<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeneficiaryAttachment extends Model
{
    protected $fillable = [
        'beneficiary_id',
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
    ];

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
