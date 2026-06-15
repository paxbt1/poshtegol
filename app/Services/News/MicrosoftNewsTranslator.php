<?php

namespace App\Services\News;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MicrosoftNewsTranslator
{
    public function __construct(private readonly NewsSettings $settings) {}

    /**
     * @return array{translated_title: ?string, translated_summary: ?string, translated_body: ?string}
     */
    public function translateArticle(string $title, ?string $description = null, ?string $source = null, ?string $content = null): array
    {
        $title = trim($title);
        $description = trim((string) $description);
        $content = trim((string) $content);

        if ($title === '') {
            return ['translated_title' => null, 'translated_summary' => null, 'translated_body' => null];
        }

        $texts = [$title];
        if ($description !== '') {
            $texts[] = $description;
        }
        if ($content !== '') {
            $texts[] = $content;
        }

        $translations = $this->translateTexts($texts);

        return [
            'translated_title' => isset($translations[0]) ? Str::limit(trim($translations[0]), 220, '') : null,
            'translated_summary' => isset($translations[1]) ? Str::limit(trim($translations[1]), 180, '') : null,
            'translated_body' => isset($translations[2]) ? trim($translations[2]) : (isset($translations[1]) ? trim($translations[1]) : null),
        ];
    }

    public function testConnection(): string
    {
        $translations = $this->translateTexts(['FIFA World Cup 2026 schedule confirmed']);

        if (empty($translations[0])) {
            throw new RuntimeException('اتصال برقرار شد اما پاسخ قابل استفاده‌ای از Microsoft Translator دریافت نشد.');
        }

        return $translations[0];
    }

    /**
     * @param array<int, string> $texts
     * @return array<int, string>
     */
    private function translateTexts(array $texts): array
    {
        $apiKey = $this->settings->microsoftTranslatorKey();
        if (! $apiKey) {
            return [];
        }

        $endpoint = $this->settings->microsoftTranslatorEndpoint();
        $headers = [
            'Ocp-Apim-Subscription-Key' => $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($region = $this->settings->microsoftTranslatorRegion()) {
            $headers['Ocp-Apim-Subscription-Region'] = $region;
        }

        $response = Http::withHeaders($headers)
            ->timeout(20)
            ->retry(1, 300)
            ->post($endpoint.'/translate?api-version=3.0&from=en&to=fa', array_map(
                fn (string $text): array => ['Text' => $text],
                $texts
            ));

        if ($response->failed()) {
            throw new RuntimeException('خطا در ارتباط با Microsoft Translator. وضعیت: '.$response->status());
        }

        $result = [];
        foreach ($response->json() ?: [] as $item) {
            $result[] = (string) data_get($item, 'translations.0.text', '');
        }

        return $result;
    }
}
