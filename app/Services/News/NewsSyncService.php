<?php

namespace App\Services\News;

use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\NewsSyncLog;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class NewsSyncService
{
    public function __construct(
        private readonly NewsSettings $settings,
        private readonly GNewsClient $client,
        private readonly GeminiHeadlineTranslator $geminiTranslator,
        private readonly Llm7NewsTranslator $llm7Translator,
        private readonly MicrosoftNewsTranslator $microsoftTranslator,
        private readonly NewsImageDownloader $imageDownloader,
    ) {}

    public function sync(bool $translateArticles = true): array
    {
        $startedAt = now();
        $provider = $this->settings->provider();
        $created = 0;
        $updated = 0;
        $translated = 0;
        $received = 0;
        $translationErrors = [];
        $seen = [];

        try {
            if ($provider !== 'gnews') {
                throw new \RuntimeException('فعلاً فقط سرویس GNews برای اخبار فعال است. سایر منابع در رجیستری قابل آماده‌سازی هستند.');
            }

            $batches = $this->buildGnewsBatches();
            foreach ($batches as $batch) {
                $articles = $this->client->fetchLatest($batch['query'], $batch['max']);
                $received += count($articles);

                foreach ($articles as $payload) {
                    $normalized = $this->normalizeGNewsArticle($payload);

                    if (! $normalized['original_title'] || ! $normalized['original_url']) {
                        continue;
                    }
                    if (isset($seen[$normalized['hash']])) {
                        continue;
                    }
                    $seen[$normalized['hash']] = true;

                    $result = $this->persistArticle($normalized, $batch['category_id'], $translationErrors, $translateArticles);
                    $created += $result['created'];
                    $updated += $result['updated'];
                    $translated += $result['translated'];
                }
            }

            $message = 'اخبار با موفقیت همگام‌سازی شد.';
            if ($translationErrors) {
                $message .= ' برخی تیترها/خلاصه‌ها بدون ترجمه ذخیره شدند.';
            }

            NewsSyncLog::create([
                'provider' => $provider,
                'status' => 'success',
                'items_received' => $received,
                'items_created' => $created,
                'items_updated' => $updated,
                'items_translated' => $translated,
                'message' => $message,
                'error_payload' => $translationErrors ? ['translation_errors' => array_slice($translationErrors, 0, 5)] : null,
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);

            return compact('provider', 'received', 'created', 'updated', 'translated') + ['status' => 'success', 'message' => $message];
        } catch (Throwable $e) {
            NewsSyncLog::create([
                'provider' => $provider,
                'status' => 'failed',
                'items_received' => $received,
                'items_created' => $created,
                'items_updated' => $updated,
                'items_translated' => $translated,
                'message' => $e->getMessage(),
                'error_payload' => [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);

            return compact('provider', 'received', 'created', 'updated', 'translated') + ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }

    private function buildGnewsBatches(): array
    {
        $batches = [[
            'query' => $this->settings->query(),
            'category_id' => null,
            'max' => $this->settings->maxPerSync(),
        ]];

        $categoryQueries = $this->settings->categoryQueries();
        if (! $categoryQueries) {
            return $batches;
        }

        $categories = NewsCategory::query()->whereIn('slug', array_keys($categoryQueries))->get()->keyBy('slug');
        $maxPerCategory = max(1, min(10, (int) ceil($this->settings->maxPerSync() / 2)));

        foreach ($categoryQueries as $slug => $query) {
            $category = $categories->get($slug);
            if (! $category) {
                continue;
            }
            $batches[] = [
                'query' => trim((string) $query),
                'category_id' => $category->id,
                'max' => $maxPerCategory,
            ];
        }

        return $batches;
    }

    private function persistArticle(array $normalized, ?int $preferredCategoryId, array &$translationErrors, bool $translateArticles): array
    {
        $article = NewsArticle::query()->where('hash', $normalized['hash'])->first();
        $wasCreated = false;
        $translated = 0;

        if (! $article) {
            $article = new NewsArticle(['hash' => $normalized['hash']]);
            $wasCreated = true;
        } elseif ($article->slug) {
            $normalized['slug'] = $article->slug;
        }

        $article->fill($normalized);
        $article->category_id = $article->category_id ?: $preferredCategoryId ?: $this->guessCategoryId($normalized['original_title'] ?? '', $normalized['original_description'] ?? '');

        if ($normalized['image_url'] && (! $article->local_image_path || $wasCreated)) {
            $localImagePath = $this->imageDownloader->download($normalized['image_url'], $normalized['hash']);
            if ($localImagePath) {
                $article->local_image_path = $localImagePath;
            }
        }

        if ($translateArticles && ($wasCreated || ! $article->translated_title || ! $article->translated_summary || (! $article->translated_body && $article->original_content))) {
            try {
                $translation = $this->translate(
                    $normalized['original_title'],
                    $normalized['original_description'] ?? null,
                    $normalized['source_name'] ?? null,
                    $normalized['original_content'] ?? null
                );

                if (! empty($translation['translated_title'])) {
                    $article->translated_title = $translation['translated_title'];
                }
                if (! empty($translation['translated_summary'])) {
                    $article->translated_summary = $translation['translated_summary'];
                }
                if (! empty($translation['translated_body'])) {
                    $article->translated_body = $translation['translated_body'];
                }
                if (! empty($translation['translated_title']) || ! empty($translation['translated_summary']) || ! empty($translation['translated_body'])) {
                    $article->translated_at = now();
                    $article->translation_status = 'translated';
                    $translated = 1;
                }
            } catch (Throwable $translationError) {
                $translationErrors[] = $translationError->getMessage();
                $article->translation_status = 'failed';
                $article->raw_payload = array_merge($normalized['raw_payload'] ?: [], [
                    '_translation_error' => $translationError->getMessage(),
                ]);
            }
        }

        $article->status = 'published';
        $article->save();

        return [
            'created' => $wasCreated ? 1 : 0,
            'updated' => $wasCreated ? 0 : 1,
            'translated' => $translated,
        ];
    }

    /**
     * @return array{translated_title: ?string, translated_summary: ?string, translated_body: ?string}
     */
    private function translate(string $title, ?string $description, ?string $source, ?string $content = null): array
    {
        $primary = $this->settings->translationProvider();
        $providers = match ($primary) {
            'llm7' => ['llm7', 'gemini', 'microsoft'],
            'microsoft' => ['microsoft', 'llm7', 'gemini'],
            default => ['gemini', 'llm7', 'microsoft'],
        };
        $lastError = null;

        foreach ($providers as $provider) {
            try {
                if ($provider === 'gemini' && $this->settings->geminiApiKey()) {
                    return $this->geminiTranslator->translateArticle($title, $description, $source, $content);
                }
                if ($provider === 'llm7' && $this->settings->llm7ApiKey()) {
                    return $this->llm7Translator->translateArticle($title, $description, $source, $content);
                }
                if ($provider === 'microsoft' && $this->settings->microsoftTranslatorKey()) {
                    return $this->microsoftTranslator->translateArticle($title, $description, $source, $content);
                }
            } catch (Throwable $e) {
                $lastError = $e;
            }
        }

        if ($lastError) {
            throw $lastError;
        }

        return ['translated_title' => null, 'translated_summary' => null, 'translated_body' => null];
    }

    private function normalizeGNewsArticle(array $payload): array
    {
        $url = Arr::get($payload, 'url');
        $title = trim((string) Arr::get($payload, 'title'));
        $description = trim((string) Arr::get($payload, 'description'));
        $content = trim((string) Arr::get($payload, 'content'));
        $publishedAt = Arr::get($payload, 'publishedAt');
        $slug = $this->makeUniqueSlug($title ?: (string) $url);

        return [
            'provider' => 'gnews',
            'external_id' => $url ? hash('sha256', (string) $url) : null,
            'source_name' => Arr::get($payload, 'source.name'),
            'source_url' => Arr::get($payload, 'source.url'),
            'original_url' => $url,
            'image_url' => Arr::get($payload, 'image'),
            'original_title' => Str::limit($title, 500, ''),
            'original_description' => $description !== '' ? $description : null,
            'original_content' => $content !== '' ? $content : null,
            'slug' => $slug,
            'language' => $this->settings->language(),
            'published_at' => $publishedAt ? Carbon::parse($publishedAt) : null,
            'fetched_at' => now(),
            'status' => 'published',
            'translation_status' => 'pending',
            'is_video' => false,
            'raw_payload' => $payload,
            'hash' => NewsArticle::makeHash('gnews', $url, $title, $publishedAt),
        ];
    }

    private function guessCategoryId(string $title, string $description): ?int
    {
        $text = Str::lower($title.' '.$description);
        $slug = match (true) {
            str_contains($text, 'world cup') || str_contains($text, 'fifa') || str_contains($text, '2026') => 'world-cup-2026',
            str_contains($text, 'iran') || str_contains($text, 'asia') || str_contains($text, 'afc') => 'iran-asia',
            str_contains($text, 'transfer') || str_contains($text, 'signing') || str_contains($text, 'contract') => 'transfers',
            str_contains($text, 'analysis') || str_contains($text, 'tactical') || str_contains($text, 'stats') || str_contains($text, 'preview') => 'analysis',
            str_contains($text, 'video') || str_contains($text, 'highlights') || str_contains($text, 'interview') => 'videos',
            default => 'world-football',
        };

        return NewsCategory::query()->where('slug', $slug)->value('id');
    }

    private function makeUniqueSlug(string $basis): string
    {
        $base = Str::slug(Str::limit($basis, 90, ''), '-');
        if ($base === '') {
            $base = 'news-'.Str::random(8);
        }

        $slug = $base;
        $i = 2;
        while (NewsArticle::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
