<?php

namespace App\Services\News;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GNewsClient
{
    public function __construct(private readonly NewsSettings $settings) {}

    public function fetchLatest(?string $queryOverride = null, ?int $maxOverride = null): array
    {
        if (! $this->settings->enabled()) {
            throw new RuntimeException('ماژول اخبار غیرفعال است.');
        }

        $apiKey = $this->settings->gnewsApiKey();
        if (! $apiKey) {
            throw new RuntimeException('کلید GNews در تنظیمات مدیریت ثبت نشده است.');
        }

        $query = trim((string) ($queryOverride ?: $this->settings->query()));
        if ($query === '') {
            throw new RuntimeException('عبارت جستجوی اخبار تنظیم نشده است.');
        }

        $params = [
            'q' => $query,
            'lang' => $this->settings->language(),
            'max' => $maxOverride ?: $this->settings->maxPerSync(),
            'sortby' => $this->settings->sortBy(),
            'apikey' => $apiKey,
        ];

        if ($this->settings->country()) {
            $params['country'] = $this->settings->country();
        }

        if ($this->settings->inFields()) {
            $params['in'] = $this->settings->inFields();
        }

        $response = Http::baseUrl($this->settings->gnewsBaseUrl())
            ->acceptJson()
            ->timeout(20)
            ->get('/search', $params);

        if ($response->failed()) {
            $message = match ($response->status()) {
                401, 403 => 'دسترسی به GNews مجاز نیست. کلید API را بررسی کنید.',
                429 => 'محدودیت درخواست GNews فعال شده است. بعداً دوباره تلاش کنید.',
                default => 'خطا در دریافت اخبار از GNews.',
            };

            throw new RuntimeException($message.' وضعیت: '.$response->status());
        }

        return $response->json('articles') ?: [];
    }
}
