<?php

namespace App\Services\FootballData;

use App\Models\AppSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FootballDataClient
{
    public function teams(): array
    {
        return $this->get('/competitions/'.$this->setting('football_data_competition_code', config('football-data.competition_code')).'/teams', [
            'season' => $this->setting('football_data_season', config('football-data.season')),
        ]);
    }

    public function fixtures(array $query = []): array
    {
        return $this->get('/competitions/'.$this->setting('football_data_competition_code', config('football-data.competition_code')).'/matches', array_merge([
            'season' => $this->setting('football_data_season', config('football-data.season')),
        ], $query));
    }

    public function match(int $id): array
    {
        return $this->get('/matches/'.$id);
    }

    private function get(string $path, array $query = []): array
    {
        if (! $this->boolSetting('football_data_enabled', (bool) config('football-data.enabled'))) {
            throw new FootballDataException('همگام‌سازی football-data غیرفعال است.');
        }

        $token = $this->setting('football_data_api_token', config('football-data.token'));
        if (! $token) {
            throw new FootballDataException('توکن football-data تنظیم نشده است.');
        }

        $response = Http::baseUrl(rtrim((string) $this->setting('football_data_base_url', config('football-data.base_url')), '/'))
            ->timeout((int) $this->setting('football_data_timeout', config('football-data.timeout', 20)))
            ->withHeaders([
                'X-Auth-Token' => $token,
                'Accept' => 'application/json',
                'X-Unfold-Goals' => 'true',
            ])
            ->get($path, $query);

        $this->logRateLimitHeaders($response);

        if ($response->failed()) {
            throw new FootballDataException(
                $this->messageForStatus($response->status()),
                $response->status(),
                $response->json() ?: [],
                $path,
            );
        }

        return $response->json() ?: [];
    }


    private function setting(string $key, mixed $default = null): mixed
    {
        return AppSetting::getValue($key, $default);
    }

    private function boolSetting(string $key, bool $default = false): bool
    {
        return filter_var($this->setting($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    }

    private function messageForStatus(int $status): string
    {
        return match (true) {
            in_array($status, [401, 403], true) => 'دسترسی به football-data مجاز نیست. توکن را بررسی کنید.',
            $status === 404 => 'داده در football-data پیدا نشد.',
            $status === 429 => 'محدودیت تعداد درخواست football-data فعال شده است.',
            $status >= 500 => 'سرویس football-data در دسترس نیست.',
            default => 'خطا در دریافت داده از football-data.',
        };
    }

    private function logRateLimitHeaders(Response $response): void
    {
        Log::info('football-data response', [
            'status' => $response->status(),
            'x-requests-available-minute' => $response->header('X-Requests-Available-Minute'),
            'x-requestcounter-reset' => $response->header('X-RequestCounter-Reset'),
        ]);
    }
}
