<?php

namespace App\Services\News;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GeminiHeadlineTranslator
{
    public function __construct(private readonly NewsSettings $settings) {}

    public function translateTitle(string $title, ?string $source = null): ?string
    {
        $result = $this->translateArticle($title, null, $source);

        return $result['translated_title'] ?? null;
    }

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

        $apiKey = $this->settings->geminiApiKey();
        if (! $apiKey) {
            return ['translated_title' => null, 'translated_summary' => null, 'translated_body' => null];
        }

        $model = $this->settings->geminiModel();
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent';

        $prompt = implode("\n", array_filter([
            'You translate English football news for a private Persian RTL FIFA World Cup 2026 web app.',
            'Return valid JSON only. No markdown. No extra explanation.',
            'JSON schema: {"translated_title":"...","translated_summary":"...","translated_body":"..."}',
            'Rules:',
            '- Translate the headline into natural Persian.',
            '- translated_summary must be very short: 1 Persian sentence, maximum 22 words.',
            '- translated_body must translate only the provided description/content. Do not invent facts or add new details.',
            '- If content is truncated or incomplete, keep the body as a concise Persian digest of only the available text.',
            '- Keep team/player/city/source names accurate.',
            $source ? 'Source: '.$source : null,
            'Title: '.$title,
            $description !== '' ? 'Description: '.$description : null,
            $content !== '' ? 'Content: '.$content : null,
        ]));

        $response = Http::acceptJson()
            ->timeout(25)
            ->retry(1, 300)
            ->post($url.'?key='.$apiKey, [
                'contents' => [[
                    'parts' => [[
                        'text' => $prompt,
                    ]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 520,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('خطا در ارتباط با Gemini. وضعیت: '.$response->status());
        }

        $text = trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text'));
        $json = $this->decodeJson($text);

        $translatedTitle = trim((string) ($json['translated_title'] ?? ''));
        $translatedSummary = trim((string) ($json['translated_summary'] ?? ''));
        $translatedBody = trim((string) ($json['translated_body'] ?? ''));

        return [
            'translated_title' => $translatedTitle !== '' ? Str::limit($translatedTitle, 220, '') : null,
            'translated_summary' => $translatedSummary !== '' ? Str::limit($translatedSummary, 180, '') : null,
            'translated_body' => $translatedBody !== '' ? $translatedBody : null,
        ];
    }

    public function testConnection(): string
    {
        $result = $this->translateArticle(
            'FIFA World Cup 2026 schedule confirmed',
            'The tournament schedule and match details are being updated for fans.',
            'Connection Test'
        );

        if (empty($result['translated_title'])) {
            throw new RuntimeException('اتصال برقرار شد اما پاسخ قابل استفاده‌ای از Gemini دریافت نشد.');
        }

        return $result['translated_title'];
    }

    private function decodeJson(string $text): array
    {
        $clean = trim($text);
        $clean = preg_replace('/^```(?:json)?\s*/u', '', $clean) ?: $clean;
        $clean = preg_replace('/\s*```$/u', '', $clean) ?: $clean;

        $decoded = json_decode($clean, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return [
            'translated_title' => trim($text, " \t\n\r\0\x0B\"'`،"),
            'translated_summary' => null,
            'translated_body' => null,
        ];
    }
}
