<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectAssistance extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'direct_assistance';

    protected $fillable = [
        'beneficiary_id',
        'program_name_id',
        'resource_type_id',
        'assistance_purpose_id',
        'quantity',
        'amount',
        'distributed_at',
        'release_outcome',
        'remarks',
        'created_by',
        'distributed_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'amount' => 'decimal:2',
        'distributed_at' => 'datetime',
    ];
}
