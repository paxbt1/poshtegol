<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsSyncLog extends Model
{
    protected $fillable = [
        'provider',
        'status',
        'items_received',
        'items_created',
        'items_updated',
        'items_translated',
        'message',
        'error_payload',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'error_payload' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
