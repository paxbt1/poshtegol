<?php

namespace App\Services\FootballData;

use App\Models\AppSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class TeamCrestDownloader
{
    public function download(?string $url, ?string $code = null): ?string
    {
        if (! filter_var(AppSetting::getValue('football_data_download_crests', config('football-data.download_crests', true) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN) || ! $url) {
            return null;
        }

        if (! Str::startsWith($url, ['https://', 'http://'])) {
            return null;
        }

        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
        if (! in_array($extension, ['svg', 'png', 'jpg', 'jpeg', 'webp'], true)) {
            $extension = 'svg';
        }

        $safeCode = strtoupper(preg_replace('/[^A-Z0-9_-]/i', '', (string) ($code ?: 'team')) ?: 'team');
        $relativePath = "media/teams/crests/{$safeCode}.{$extension}";
        $absolutePath = public_path($relativePath);

        try {
            if (File::exists($absolutePath) && File::size($absolutePath) > 0) {
                return $relativePath;
            }

            $response = Http::timeout(15)
                ->accept('*/*')
                ->get($url);

            if (! $response->successful() || $response->body() === '') {
                return null;
            }

            File::ensureDirectoryExists(dirname($absolutePath));
            File::put($absolutePath, $response->body());

            return $relativePath;
        } catch (Throwable) {
            return null;
        }
    }
}
