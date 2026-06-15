<?php

namespace App\Services\News;

use App\Models\AppSetting;

class NewsSettings
{
    public function enabled(): bool
    {
        return filter_var($this->get('news_enabled', config('news.enabled', false) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    }

    public function provider(): string
    {
        return strtolower((string) $this->get('news_provider', config('news.provider', 'gnews')));
    }

    public function translationProvider(): string
    {
        $provider = strtolower((string) $this->get('news_translation_provider', config('news.translation_provider', 'gemini')));

        return in_array($provider, ['gemini', 'llm7', 'microsoft'], true) ? $provider : 'gemini';
    }

    public function gnewsApiKey(): ?string
    {
        $value = trim((string) $this->get('gnews_api_key', config('news.gnews_api_key', '')));

        return $value !== '' ? $value : null;
    }

    public function gnewsBaseUrl(): string
    {
        return rtrim((string) $this->get('gnews_base_url', config('news.gnews_base_url', 'https://gnews.io/api/v4')), '/');
    }

    public function query(): string
    {
        return trim((string) $this->get('news_query', config('news.query', 'FIFA World Cup 2026 OR World Cup 2026')));
    }

    public function language(): string
    {
        return strtolower((string) $this->get('news_language', config('news.language', 'en')));
    }

    public function country(): ?string
    {
        $value = strtolower(trim((string) $this->get('news_country', config('news.country', ''))));

        return $value !== '' ? $value : null;
    }

    public function maxPerSync(): int
    {
        return max(1, min(25, (int) $this->get('news_max_per_sync', config('news.max_per_sync', 8))));
    }

    public function categoryQueriesEnabled(): bool
    {
        return filter_var($this->get('news_category_queries_enabled', config('news.category_queries_enabled', false) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    }

    public function downloadImages(): bool
    {
        return filter_var($this->get('news_download_images', config('news.download_images', true) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    }

    public function sortBy(): string
    {
        $value = (string) $this->get('news_sort_by', config('news.sort_by', 'publishedAt'));

        return in_array($value, ['publishedAt', 'relevance'], true) ? $value : 'publishedAt';
    }

    public function inFields(): ?string
    {
        $value = trim((string) $this->get('news_in_fields', config('news.in_fields', 'title,description')));

        return $value !== '' ? $value : null;
    }

    public function geminiApiKey(): ?string
    {
        $value = trim((string) $this->get('gemini_api_key', config('news.gemini_api_key', '')));

        return $value !== '' ? $value : null;
    }

    public function geminiModel(): string
    {
        return trim((string) $this->get('gemini_model', config('news.gemini_model', 'gemini-2.0-flash-lite'))) ?: 'gemini-2.0-flash-lite';
    }

    public function llm7ApiKey(): ?string
    {
        $value = trim((string) $this->get('llm7_api_key', config('news.llm7_api_key', '')));

        return $value !== '' ? $value : null;
    }

    public function llm7BaseUrl(): string
    {
        return rtrim((string) $this->get('llm7_base_url', config('news.llm7_base_url', 'https://api.llm7.io/v1')), '/');
    }

    public function llm7Model(): string
    {
        return trim((string) $this->get('llm7_model', config('news.llm7_model', 'default'))) ?: 'default';
    }

    public function microsoftTranslatorKey(): ?string
    {
        $value = trim((string) $this->get('microsoft_translator_key', config('news.microsoft_translator_key', '')));

        return $value !== '' ? $value : null;
    }

    public function microsoftTranslatorRegion(): ?string
    {
        $value = trim((string) $this->get('microsoft_translator_region', config('news.microsoft_translator_region', '')));

        return $value !== '' ? $value : null;
    }

    public function microsoftTranslatorEndpoint(): string
    {
        return rtrim((string) $this->get('microsoft_translator_endpoint', config('news.microsoft_translator_endpoint', 'https://api.cognitive.microsofttranslator.com')), '/');
    }


    public function categoryQueries(): array
    {
        if (! $this->categoryQueriesEnabled()) {
            return [];
        }

        $default = [
            'world-cup-2026' => 'FIFA World Cup 2026 OR World Cup 2026',
            'world-football' => 'football OR soccer OR UEFA OR Premier League OR Champions League',
            'iran-asia' => 'Iran football OR AFC football OR Asian football',
            'transfers' => 'football transfer OR signing OR contract',
            'analysis' => 'football analysis OR tactics OR stats',
            'videos' => 'football video OR highlights OR interview',
        ];

        $value = (string) $this->get('news_category_queries_json', json_encode($default));
        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_filter($decoded, fn ($query) => is_string($query) && trim($query) !== '') : $default;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return AppSetting::getValue($key, $default);
    }
}
