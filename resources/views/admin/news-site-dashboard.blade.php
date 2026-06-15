@extends('layouts.news-admin')

@section('content')
<h1 class="title">داشبورد مدیریت خبری پشت گل</h1>
<p class="muted" style="line-height:1.9;margin-top:8px;">این پنل فقط برای سایت خبری پشت گل است و از پنل کاپ خانوادگی جدا شده است.</p>

<div class="grid desktop-grid-3" style="margin-top:16px;">
    <x-stat-card label="کل خبرها" :value="$articlesCount" hint="همه خبرهای دریافت‌شده" />
    <x-stat-card label="منتشرشده" :value="$publishedCount" hint="نمایش در سایت" />
    <x-stat-card label="دسته فعال" :value="$categoriesCount" hint="منوی سایت" />
    <x-stat-card label="تبلیغ فعال" :value="$activeAdsCount" hint="بنرهای نمایشی" />
    <x-stat-card label="منبع فعال" :value="$activeSourcesCount" hint="خبر/داده فوتبال" />
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">دسترسی سریع</h2>
        <div class="admin-action-row">
            <a class="btn btn-primary" href="{{ route('news-admin.news.index') }}">مدیریت اخبار</a>
            <a class="btn btn-outline" href="{{ route('news-admin.settings') }}">تنظیمات سرویس‌ها</a>
        </div>
    </x-ui.card>
</div>

<div class="grid desktop-grid-2" style="margin-top:16px;">
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">آخرین خبرها</h2>
        <div class="admin-list-mini">
            @forelse($latestArticles as $article)
                <a href="{{ \Illuminate\Support\Facades\Route::has('public.news.show') ? route('public.news.show', $article) : '#' }}" target="_blank">
                    <strong>{{ $article->display_title }}</strong>
                    <span>{{ $article->category?->title ?: 'بدون دسته' }} · {{ $article->status }}</span>
                </a>
            @empty
                <p class="muted">هنوز خبری دریافت نشده است.</p>
            @endforelse
        </div>
    </x-ui.card>

    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">آخرین همگام‌سازی‌ها</h2>
        <div class="admin-list-mini">
            @forelse($latestLogs as $log)
                <div class="mini-log {{ $log->status }}">
                    <strong>{{ $log->status === 'success' ? 'موفق' : 'ناموفق' }} · {{ $log->provider }}</strong>
                    <span>{{ $log->message }} · {{ \App\Support\Jalali::format($log->created_at, 'Y/m/d H:i') }}</span>
                </div>
            @empty
                <p class="muted">لاگی ثبت نشده است.</p>
            @endforelse
        </div>
    </x-ui.card>
</div>
@endsection
