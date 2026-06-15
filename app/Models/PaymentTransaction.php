<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'prediction_entry_id',
        'gateway',
        'amount',
        'amount_gateway',
        'entry_amount',
        'gateway_fee_amount',
        'transaction_id',
        'reference_id',
        'status',
        'callback_payload',
        'request_payload',
        'paid_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'callback_payload' => 'array',
            'request_payload' => 'array',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function predictionEntry(): BelongsTo
    {
        return $this->belongsTo(PredictionEntry::class);
    }
}
