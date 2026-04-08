<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    protected $fillable = [
        'beneficiary_id',
        'message',
        'status',
        'delivery_status',
        'response',
        'gateway_message_id',
        'sent_at',
        'callback_received_at',
        'retry_count',
    ];

    protected function casts(): array
    {
        return [
            'sent_at'              => 'datetime',
            'callback_received_at' => 'datetime',
            'retry_count'          => 'integer',
        ];
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    /**
     * Check if SMS was successfully delivered
     */
    public function isDelivered(): bool
    {
        return $this->delivery_status === 'delivered';
    }

    /**
     * Check if SMS delivery failed
     */
    public function isFailed(): bool
    {
        return $this->delivery_status === 'failed' || $this->delivery_status === 'undeliverable';
    }

    /**
     * Check if SMS is still pending delivery confirmation
     */
    public function isPending(): bool
    {
        return $this->delivery_status === 'pending';
    }
}
