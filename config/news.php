<?php

return [
    'enabled' => env('NEWS_ENABLED', false),
    'provider' => env('NEWS_PROVIDER', 'gnews'),
    'gnews_base_url' => env('GNEWS_BASE_URL', 'https://gnews.io/api/v4'),
    'gnews_api_key' => env('GNEWS_API_KEY', ''),
    'query' => env('NEWS_QUERY', 'FIFA World Cup 2026 OR World Cup 2026'),
    'language' => env('NEWS_LANGUAGE', 'en'),
    'country' => env('NEWS_COUNTRY', ''),
    'max_per_sync' => env('NEWS_MAX_PER_SYNC', 8),
    'download_images' => env('NEWS_DOWNLOAD_IMAGES', true),
    'sort_by' => env('NEWS_SORT_BY', 'publishedAt'),
    'in_fields' => env('NEWS_IN_FIELDS', 'title,description'),
    'translation_provider' => env('NEWS_TRANSLATION_PROVIDER', 'gemini'),
    'gemini_api_key' => env('GEMINI_API_KEY', ''),
    'gemini_model' => env('GEMINI_MODEL', 'gemini-2.0-flash-lite'),
    'microsoft_translator_key' => env('MICROSOFT_TRANSLATOR_KEY', ''),
    'microsoft_translator_region' => env('MICROSOFT_TRANSLATOR_REGION', ''),
    'microsoft_translator_endpoint' => env('MICROSOFT_TRANSLATOR_ENDPOINT', 'https://api.cognitive.microsofttranslator.com'),
];
