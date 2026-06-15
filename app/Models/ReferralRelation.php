<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralRelation extends Model
{
    protected $fillable = ['inviter_user_id', 'referred_user_id', 'source', 'active_until'];

    protected function casts(): array
    {
        return ['active_until' => 'datetime'];
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_user_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
