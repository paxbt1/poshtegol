<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsSource extends Model
{
    protected $fillable = [
        'key', 'name', 'type', 'is_active', 'is_official', 'is_unofficial',
        'requires_key', 'settings', 'priority', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_official' => 'boolean',
            'is_unofficial' => 'boolean',
            'requires_key' => 'boolean',
            'settings' => 'array',
            'priority' => 'integer',
            'last_synced_at' => 'datetime',
        ];
    }
}
