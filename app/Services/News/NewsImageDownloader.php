<?php

namespace App\Services\News;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class NewsImageDownloader
{
    public function __construct(private readonly NewsSettings $settings) {}

    public function download(?string $url, ?string $hash = null): ?string
    {
        if (! $this->settings->downloadImages() || ! $url) {
            return null;
        }

        if (! Str::startsWith($url, ['https://', 'http://'])) {
            return null;
        }

        try {
            $response = Http::timeout(18)
                ->accept('image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8')
                ->get($url);

            if (! $response->successful() || $response->body() === '') {
                return null;
            }

            $contentType = strtolower((string) $response->header('Content-Type'));
            $body = $response->body();

            if (strlen($body) > 3 * 1024 * 1024) {
                return null;
            }

            $extension = $this->extensionFromContentType($contentType)
                ?: strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));

            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) {
                $extension = 'jpg';
            }

            $fileName = ($hash ?: sha1($url)).'.'.$extension;
            $relativePath = 'media/news/images/'.$fileName;
            $absolutePath = public_path($relativePath);

            if (File::exists($absolutePath) && File::size($absolutePath) > 0) {
                return $relativePath;
            }

            File::ensureDirectoryExists(dirname($absolutePath));
            File::put($absolutePath, $body);

            return $relativePath;
        } catch (Throwable) {
            return null;
        }
    }

    private function extensionFromContentType(string $contentType): ?string
    {
        return match (true) {
            str_contains($contentType, 'image/jpeg') => 'jpg',
            str_contains($contentType, 'image/png') => 'png',
            str_contains($contentType, 'image/webp') => 'webp',
            str_contains($contentType, 'image/gif') => 'gif',
            str_contains($contentType, 'image/svg') => 'svg',
            default => null,
        };
    }
}
