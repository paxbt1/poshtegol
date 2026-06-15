<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InviteLink extends Model
{
    public const TYPE_MASTER_ACCESS = 'master_access';
    public const TYPE_USER_REFERRAL = 'user_referral';

    protected $fillable = [
        'code',
        'owner_user_id',
        'type',
        'title',
        'is_active',
        'earns_commission',
        'max_uses',
        'used_count',
        'expires_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'earns_commission' => 'boolean',
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function isUsable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return $this->max_uses === null || $this->used_count < $this->max_uses;
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_MASTER_ACCESS => 'دسترسی مادر',
            self::TYPE_USER_REFERRAL => 'دعوت کاربر',
            default => 'نامشخص',
        };
    }
}
