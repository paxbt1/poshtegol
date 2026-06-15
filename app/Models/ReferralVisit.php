<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralVisit extends Model
{
    protected $fillable = [
        'inviter_user_id',
        'invite_code',
        'ip_address',
        'user_agent',
        'converted_user_id',
        'converted_at',
    ];

    protected function casts(): array
    {
        return ['converted_at' => 'datetime'];
    }
}
