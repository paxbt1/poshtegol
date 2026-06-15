<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'password',
        'card_number',
        'card_hash',
        'card_last4',
        'invite_code',
        'invited_by_user_id',
        'registered_via_invite_code',
        'registered_via_invite_type',
        'direct_referrer_user_id',
        'is_admin',
        'is_active',
        'mobile_verified_at',
    ];

    protected $hidden = [
        'password',
        'card_number',
        'card_hash',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'card_number' => 'encrypted',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'mobile_verified_at' => 'datetime',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: 'عضو خانواده';
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function invitedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'direct_referrer_user_id');
    }

    public function directReferrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'direct_referrer_user_id');
    }

    public function inviteLinks(): HasMany
    {
        return $this->hasMany(InviteLink::class, 'owner_user_id');
    }

    public function predictionEntries(): HasMany
    {
        return $this->hasMany(PredictionEntry::class);
    }
}
