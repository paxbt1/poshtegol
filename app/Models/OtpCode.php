<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = [
        'mobile',
        'code_hash',
        'expires_at',
        'attempts',
        'used_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}
