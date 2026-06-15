<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FootballDataSyncLog extends Model
{
    protected $fillable = [
        'type',
        'status',
        'requested_url',
        'http_status',
        'items_received',
        'items_created',
        'items_updated',
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
