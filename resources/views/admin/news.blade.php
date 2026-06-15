@php($newsAdmin = request()->routeIs('news-admin.*'))
@extends($newsAdmin ? 'layouts.news-admin' : 'layouts.admin')

@section('content')
<h1 class="title">مدیریت اخبار پشت گل</h1>
<p class="muted" style="line-height:1.9;margin-top:8px;">اخبار پیش‌فرض منتشر می‌شوند؛ مدیر می‌تواند دسته‌بندی، تیتر فارسی، خلاصه، متن قابل نمایش، تصویر محلی و وضعیت انتشار را ویرایش کند.</p>

<div class="grid desktop-grid-3" style="margin-top:16px;">
    <x-stat-card label="کل خبرها" :value="$articles->count()" hint="آخرین ۵۰ مورد" />
    <x-stat-card label="منتشرشده" :value="$articles->where('status', 'published')->count()" hint="قابل نمایش" />
    <x-stat-card label="ترجمه‌شده" :value="$articles->whereNotNull('translated_title')->count()" hint="با مترجم" />
</div>

<x-ui.card style="margin-top:16px;">
    <div class="admin-user-row">
        <div>
            <h2 class="section-title" style="margin-top:0;">همگام‌سازی دستی</h2>
            <p class="muted">برای اجرای خودکار هر یک ساعت، کرون Laravel Scheduler باید فعال باشد.</p>
        </div>
        <div class="admin-action-row">
            <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.sync' : 'admin.news.sync') }}">@csrf<button class="btn btn-primary" type="submit">دریافت خبرها و دانلود تصاویر</button></form>
            <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.test-gemini' : 'admin.news.test-gemini') }}">@csrf<button class="btn btn-outline" type="submit">تست Gemini</button></form>
            <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.test-microsoft' : 'admin.news.test-microsoft') }}">@csrf<button class="btn btn-outline" type="submit">تست Microsoft</button></form>
        </div>
    </div>
</x-ui.card>

<div class="grid" style="margin-top:16px;">
@forelse($articles as $article)
    <x-ui.card>
        <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.update' : 'admin.news.update', $article) }}" enctype="multipart/form-data" class="admin-news-edit">
            @csrf @method('PATCH')
            <div class="admin-news-thumb">
                @if($article->display_image_url)
                    <img src="{{ $article->display_image_url }}" alt="">
                @else
                    <span>بدون تصویر</span>
                @endif
            </div>
            <div class="admin-news-fields">
                <div class="grid desktop-grid-2">
                    <div class="field"><label>تیتر فارسی</label><input class="input" name="translated_title" value="{{ $article->translated_title ?: $article->original_title }}"></div>
                    <div class="field"><label>دسته‌بندی</label><select class="input" name="category_id"><option value="">بدون دسته</option>@foreach(($categories ?? collect()) as $cat)<option value="{{ $cat->id }}" @selected($article->category_id === $cat->id)>{{ $cat->title }}</option>@endforeach</select></div>
                </div>
                <div class="field"><label>خلاصه فارسی</label><textarea class="input" name="translated_summary" style="height:82px;padding-top:10px;">{{ $article->translated_summary ?: $article->original_description }}</textarea></div>
                <div class="field"><label>متن قابل نمایش در صفحه خبر</label><textarea class="input" name="translated_body" style="height:130px;padding-top:10px;">{{ $article->translated_body ?: $article->original_content }}</textarea></div>
                <div class="grid desktop-grid-3">
                    <div class="field"><label>وضعیت</label><select class="input" name="status"><option value="published" @selected($article->status === 'published')>منتشر شده</option><option value="draft" @selected($article->status === 'draft')>پیش‌نویس</option><option value="hidden" @selected($article->status === 'hidden')>مخفی</option></select></div>
                    <div class="field"><label>تصویر محلی جدید</label><input class="input" type="file" name="local_image_file" accept="image/*"></div>
                    <div class="field"><label>لینک منبع</label><input class="input" dir="ltr" name="original_url" value="{{ $article->original_url }}"></div>
                </div>
                <label class="toggle-row compact"><input type="checkbox" name="is_featured" value="1" @checked($article->is_featured)> <span>خبر ویژه</span></label>
                <div class="muted small" style="line-height:1.8;">منبع: {{ $article->safe_source_name }} · تاریخ: {{ \App\Support\Jalali::format($article->published_at, 'Y/m/d H:i') }} · تیتر اصلی: {{ $article->original_title }}</div>
                <div class="admin-action-row"><button class="btn btn-primary" type="submit">ذخیره خبر</button><a class="btn btn-outline" target="_blank" href="{{ route('public.news.show', $article) }}">نمایش</a></div>
            </div>
        </form>
        <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.news.destroy' : 'admin.news.destroy', $article) }}" onsubmit="return confirm('این خبر حذف شود؟')" style="margin-top:10px;">
            @csrf @method('DELETE')
            <button class="btn btn-soft" type="submit">حذف خبر</button>
        </form>
    </x-ui.card>
@empty
    <x-ui.card><p class="muted">هنوز خبری دریافت نشده است.</p></x-ui.card>
@endforelse
</div>

<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">آخرین لاگ‌ها</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>وضعیت</th><th>دریافتی</th><th>ایجاد</th><th>به‌روزرسانی</th><th>ترجمه</th><th>پیام</th><th>زمان</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr><td>{{ $log->status }}</td><td>{{ $log->items_received }}</td><td>{{ $log->items_created }}</td><td>{{ $log->items_updated }}</td><td>{{ $log->items_translated }}</td><td>{{ $log->message }}</td><td>{{ \App\Support\Jalali::format($log->created_at, 'Y/m/d H:i') }}</td></tr>
                @empty
                    <tr><td colspan="7" class="muted">لاگی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
