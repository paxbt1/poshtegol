<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialLedger extends Model
{
    protected $fillable = [
        'user_id',
        'period_id',
        'source_type',
        'source_id',
        'type',
        'direction',
        'amount',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
