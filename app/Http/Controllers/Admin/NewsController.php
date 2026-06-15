<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\NewsSyncLog;
use App\Services\News\GeminiHeadlineTranslator;
use App\Services\News\Llm7NewsTranslator;
use App\Services\News\MicrosoftNewsTranslator;
use App\Services\News\NewsSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index()
    {
        return view('admin.news', [
            'articles' => Schema::hasTable('news_articles') ? NewsArticle::with('category')->latest('published_at')->latest()->take(50)->get() : collect(),
            'categories' => Schema::hasTable('news_categories') ? NewsCategory::where('is_active', true)->orderBy('sort_order')->get() : collect(),
            'logs' => Schema::hasTable('news_sync_logs') ? NewsSyncLog::query()->latest()->take(12)->get() : collect(),
        ]);
    }

    public function sync(NewsSyncService $syncService)
    {
        $result = $syncService->sync();
        $route = request()->routeIs('news-admin.*') ? 'news-admin.news.index' : 'admin.news.index';

        return response()->json([
            'message' => $result['message'],
            'result' => $result,
            'redirect' => route($route),
        ], $result['status'] === 'success' ? 200 : 422);
    }

    public function testGemini(GeminiHeadlineTranslator $translator)
    {
        try {
            $sample = $translator->testConnection();

            return response()->json([
                'message' => 'اتصال Gemini با موفقیت تست شد. نمونه ترجمه: '.$sample,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'تست اتصال Gemini ناموفق بود: '.$e->getMessage(),
            ], 422);
        }
    }

    public function testLlm7(Llm7NewsTranslator $translator)
    {
        try {
            $sample = $translator->testConnection();

            return response()->json([
                'message' => 'LLM7 connection test succeeded. Sample translation: '.$sample,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'LLM7 connection test failed: '.$e->getMessage(),
            ], 422);
        }
    }

    public function testMicrosoft(MicrosoftNewsTranslator $translator)
    {
        try {
            $sample = $translator->testConnection();

            return response()->json([
                'message' => 'اتصال Microsoft Translator با موفقیت تست شد. نمونه ترجمه: '.$sample,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'تست اتصال Microsoft Translator ناموفق بود: '.$e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, NewsArticle $article)
    {
        $data = $request->validate([
            'status' => ['required', 'in:draft,published,hidden'],
            'is_featured' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'exists:news_categories,id'],
            'translated_title' => ['nullable', 'string', 'max:500'],
            'translated_summary' => ['nullable', 'string', 'max:1200'],
            'translated_body' => ['nullable', 'string', 'max:12000'],
            'original_url' => ['nullable', 'url', 'max:1200'],
            'local_image_file' => ['nullable', 'image', 'max:4096'],
        ]);

        $payload = [
            'status' => $data['status'],
            'is_featured' => $request->boolean('is_featured'),
            'category_id' => $data['category_id'] ?? null,
            'translated_title' => trim((string) ($data['translated_title'] ?? '')) ?: null,
            'translated_summary' => trim((string) ($data['translated_summary'] ?? '')) ?: null,
            'translated_body' => trim((string) ($data['translated_body'] ?? '')) ?: null,
            'original_url' => $data['original_url'] ?? $article->original_url,
        ];

        if ($request->hasFile('local_image_file')) {
            $payload['local_image_path'] = $this->storeLocalImage($request, 'local_image_file', 'media/news/images');
        }

        $article->update($payload);

        $route = $request->routeIs('news-admin.*') ? 'news-admin.news.index' : 'admin.news.index';

        return response()->json(['message' => 'خبر به‌روزرسانی شد.', 'redirect' => route($route)]);
    }

    public function destroy(NewsArticle $article)
    {
        $article->delete();
        $route = request()->routeIs('news-admin.*') ? 'news-admin.news.index' : 'admin.news.index';

        return response()->json(['message' => 'خبر حذف شد.', 'redirect' => route($route)]);
    }

    private function storeLocalImage(Request $request, string $field, string $directory): string
    {
        $file = $request->file($field);
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) {
            $extension = 'jpg';
        }

        $relativePath = trim($directory, '/').'/'.Str::uuid().'.'.$extension;
        $absolutePath = public_path($relativePath);
        File::ensureDirectoryExists(dirname($absolutePath));
        $file->move(dirname($absolutePath), basename($absolutePath));

        return $relativePath;
    }
}
