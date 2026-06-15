<?php

namespace App\Services\News;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class Llm7NewsTranslator
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

        $apiKey = $this->settings->llm7ApiKey();
        if (! $apiKey) {
            return ['translated_title' => null, 'translated_summary' => null, 'translated_body' => null];
        }

        $prompt = implode("\n", array_filter([
            'Translate English football news into natural Persian for an RTL FIFA World Cup 2026 news website.',
            'Return JSON only with these keys: translated_title, translated_summary, translated_body.',
            'translated_summary must be one short Persian sentence, maximum 22 words.',
            'Do not invent facts. Keep names, teams, cities, and source names accurate.',
            $source ? 'Source: '.$source : null,
            'Title: '.$title,
            $description !== '' ? 'Description: '.$description : null,
            $content !== '' ? 'Content: '.$content : null,
        ]));

        $response = Http::baseUrl($this->settings->llm7BaseUrl())
            ->withToken($apiKey)
            ->acceptJson()
            ->timeout(35)
            ->retry(1, 500)
            ->post('/chat/completions', [
                'model' => $this->settings->llm7Model(),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a precise Persian sports news translator. Return valid JSON only.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
            ]);

        if ($response->failed()) {
            Log::warning('llm7 request failed', [
                'status' => $response->status(),
                'body' => mb_substr($response->body(), 0, 1000),
                'retry-after' => $response->header('Retry-After'),
                'model' => $this->settings->llm7Model(),
            ]);

            throw new RuntimeException('LLM7 request failed. Status: '.$response->status());
        }

        $text = trim((string) data_get($response->json(), 'choices.0.message.content'));
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
            throw new RuntimeException('LLM7 connected, but no usable response was returned.');
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

        if (preg_match('/\{.*\}/su', $clean, $match)) {
            $decoded = json_decode($match[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [
            'translated_title' => trim($text, " \t\n\r\0\x0B\"'`،"),
            'translated_summary' => null,
            'translated_body' => null,
        ];
    }
}
