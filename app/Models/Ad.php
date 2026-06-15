<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ad extends Model
{
    protected $fillable = [
        'ad_slot_id', 'title', 'body_text', 'cta_text', 'image_desktop', 'image_mobile', 'link_url',
        'target_blank', 'rel_nofollow', 'rel_sponsored', 'starts_at', 'ends_at',
        'is_active', 'impressions_count', 'clicks_count', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'target_blank' => 'boolean',
            'rel_nofollow' => 'boolean',
            'rel_sponsored' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'impressions_count' => 'integer',
            'clicks_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(AdSlot::class, 'ad_slot_id');
    }
}
