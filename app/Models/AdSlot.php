<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdSlot extends Model
{
    protected $fillable = ['key', 'title', 'placement', 'device', 'width', 'height', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    public function activeAds(): HasMany
    {
        return $this->ads()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order');
    }
}
