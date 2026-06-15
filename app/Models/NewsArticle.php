<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NewsArticle extends Model
{
    protected $fillable = [
        'provider',
        'external_id',
        'source_name',
        'source_url',
        'original_url',
        'image_url',
        'local_image_path',
        'original_title',
        'original_description',
        'original_content',
        'translated_title',
        'translated_summary',
        'translated_body',
        'slug',
        'language',
        'published_at',
        'fetched_at',
        'translated_at',
        'status',
        'is_featured',
        'raw_payload',
        'hash',
        'category_id',
        'duplicate_of_article_id',
        'translation_status',
        'is_video',
        'video_url',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'fetched_at' => 'datetime',
            'translated_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_video' => 'boolean',
            'raw_payload' => 'array',
        ];
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicate_of_article_id');
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->translated_title ?: $this->original_title;
    }

    public function getDisplaySummaryAttribute(): string
    {
        return $this->translated_summary
            ?: $this->original_description
            ?: Str::limit(strip_tags((string) $this->original_content), 180);
    }

    public function getDisplayBodyAttribute(): string
    {
        return trim((string) ($this->translated_body
            ?: $this->original_content
            ?: $this->original_description
            ?: $this->translated_summary
            ?: ''));
    }

    public function getDisplayImageUrlAttribute(): ?string
    {
        if ($this->local_image_path) {
            return asset(ltrim((string) $this->local_image_path, '/'));
        }

        return null;
    }

    public function getSafeSourceNameAttribute(): string
    {
        return $this->source_name ?: parse_url((string) $this->original_url, PHP_URL_HOST) ?: 'منبع خبر';
    }


    public static function makeHash(string $provider, ?string $url, ?string $title, ?string $publishedAt = null): string
    {
        $basis = $provider.'|'.($url ?: '').'|'.Str::lower(trim((string) $title)).'|'.($publishedAt ?: '');

        return hash('sha256', $basis);
    }
}
