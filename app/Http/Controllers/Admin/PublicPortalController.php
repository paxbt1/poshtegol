<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdSlot;
use App\Models\NewsCategory;
use App\Models\NewsSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicPortalController extends Controller
{
    public function categories()
    {
        return view('admin.public-categories', [
            'categories' => NewsCategory::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', 'unique:news_categories,slug'],
            'description' => ['nullable', 'string', 'max:600'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        NewsCategory::create([
            'title' => $data['title'],
            'slug' => $data['slug'] ?: Str::slug($data['title']) ?: 'category-'.Str::random(6),
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 100,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'دسته‌بندی ساخته شد.', 'redirect' => route($this->routeName('public.categories'))]);
    }

    public function updateCategory(Request $request, NewsCategory $category)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', Rule::unique('news_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:600'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update($data + ['is_active' => $request->boolean('is_active')]);

        return response()->json(['message' => 'دسته‌بندی به‌روزرسانی شد.']);
    }

    public function ads()
    {
        return view('admin.public-ads', [
            'slots' => AdSlot::query()->with('ads')->orderBy('sort_order')->get(),
        ]);
    }

    public function storeAd(Request $request)
    {
        $data = $request->validate([
            'ad_slot_id' => ['required', 'exists:ad_slots,id'],
            'title' => ['required', 'string', 'max:160'],
            'body_text' => ['nullable', 'string', 'max:1000'],
            'cta_text' => ['nullable', 'string', 'max:80'],
            'image_desktop' => ['nullable', 'string', 'max:1000'],
            'image_mobile' => ['nullable', 'string', 'max:1000'],
            'image_desktop_file' => ['nullable', 'image', 'max:4096'],
            'image_mobile_file' => ['nullable', 'image', 'max:4096'],
            'link_url' => ['nullable', 'url', 'max:1000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image_desktop_file')) {
            $data['image_desktop'] = $this->storeUploadedImage($request, 'image_desktop_file');
        }
        if ($request->hasFile('image_mobile_file')) {
            $data['image_mobile'] = $this->storeUploadedImage($request, 'image_mobile_file');
        }
        unset($data['image_desktop_file'], $data['image_mobile_file']);

        Ad::create($data + [
            'is_active' => $request->boolean('is_active', true),
            'target_blank' => true,
            'rel_nofollow' => true,
            'rel_sponsored' => true,
        ]);

        return response()->json(['message' => 'تبلیغ ثبت شد.', 'redirect' => route($this->routeName('public.ads'))]);
    }

    public function updateAd(Request $request, Ad $ad)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body_text' => ['nullable', 'string', 'max:1000'],
            'cta_text' => ['nullable', 'string', 'max:80'],
            'image_desktop' => ['nullable', 'string', 'max:1000'],
            'image_mobile' => ['nullable', 'string', 'max:1000'],
            'image_desktop_file' => ['nullable', 'image', 'max:4096'],
            'image_mobile_file' => ['nullable', 'image', 'max:4096'],
            'link_url' => ['nullable', 'url', 'max:1000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image_desktop_file')) {
            $data['image_desktop'] = $this->storeUploadedImage($request, 'image_desktop_file');
        }
        if ($request->hasFile('image_mobile_file')) {
            $data['image_mobile'] = $this->storeUploadedImage($request, 'image_mobile_file');
        }
        unset($data['image_desktop_file'], $data['image_mobile_file']);

        $ad->update($data + ['is_active' => $request->boolean('is_active')]);

        return response()->json(['message' => 'تبلیغ به‌روزرسانی شد.']);
    }

    public function destroyAd(Ad $ad)
    {
        $ad->delete();

        return response()->json(['message' => 'تبلیغ حذف شد.', 'redirect' => route($this->routeName('public.ads'))]);
    }

    public function sources()
    {
        return view('admin.public-sources', [
            'sources' => NewsSource::query()->orderBy('priority')->get(),
        ]);
    }

    public function updateSource(Request $request, NewsSource $source)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', Rule::in(['news', 'rss', 'sports', 'media'])],
            'priority' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'string', 'max:4000'],
        ]);

        $source->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'priority' => $data['priority'],
            'is_active' => $request->boolean('is_active'),
            'settings' => $data['settings'] ? json_decode($data['settings'], true) : [],
        ]);

        return response()->json(['message' => 'منبع به‌روزرسانی شد.']);
    }

    private function storeUploadedImage(Request $request, string $field): string
    {
        $file = $request->file($field);
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $extension = 'jpg';
        }

        $relativePath = 'media/ads/'.Str::uuid().'.'.$extension;
        $absolutePath = public_path($relativePath);
        File::ensureDirectoryExists(dirname($absolutePath));
        $file->move(dirname($absolutePath), basename($absolutePath));

        return '/'.$relativePath;
    }

    private function routeName(string $name): string
    {
        return request()->routeIs('news-admin.*') ? 'news-admin.'.$name : 'admin.'.$name;
    }

}
