@php($newsAdmin = request()->routeIs('news-admin.*'))
@extends($newsAdmin ? 'layouts.news-admin' : 'layouts.admin')

@section('content')
<h1 class="title">منابع خبر، مدیا و نتایج</h1>
<p class="muted">این صفحه رجیستری سرویس‌های پشت گل است. کلیدهای اصلی از تنظیمات سایت خبری خوانده می‌شوند و فعال/غیرفعال بودن، اولویت و تنظیمات اختصاصی هر منبع از اینجا مدیریت می‌شود.</p>

<div class="grid" style="margin-top:16px;">
@foreach($sources as $source)
    <x-ui.card>
        <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.public.sources.update' : 'admin.public.sources.update', $source) }}" class="grid grid-2">
            @csrf @method('PATCH')
            <div><span class="brand-pill">{{ $source->key }}</span> @if($source->is_unofficial)<span class="status-badge warning">غیررسمی</span>@endif @if($source->requires_key)<span class="status-badge">نیازمند کلید</span>@endif</div>
            <label class="toggle-row compact"><input type="checkbox" name="is_active" value="1" @checked($source->is_active)><span>فعال</span></label>
            <div class="field"><label>نام</label><input class="input" name="name" value="{{ $source->name }}" required></div>
            <div class="field"><label>نوع</label><select class="input" name="type"><option value="news" @selected($source->type==='news')>خبر</option><option value="sports" @selected($source->type==='sports')>نتایج/داده فوتبال</option><option value="media" @selected($source->type==='media')>مدیا</option><option value="rss" @selected($source->type==='rss')>RSS</option></select></div>
            <div class="field"><label>اولویت</label><input class="input" name="priority" value="{{ $source->priority }}"></div>
            <div class="field" style="grid-column:1/-1;"><label>تنظیمات JSON اختصاصی</label><textarea class="input mono" name="settings" dir="ltr" style="height:120px; padding-top:10px;">{{ json_encode($source->settings ?: [], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) }}</textarea></div>
            <button class="btn btn-outline" type="submit" style="grid-column:1/-1;">ذخیره منبع</button>
        </form>
    </x-ui.card>
@endforeach
</div>
@endsection
