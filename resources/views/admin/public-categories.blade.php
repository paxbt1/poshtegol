@php($newsAdmin = request()->routeIs('news-admin.*'))
@extends($newsAdmin ? 'layouts.news-admin' : 'layouts.admin')

@section('content')
<h1 class="title">مدیریت دسته‌بندی‌های پشت گل</h1>
<p class="muted">دسته‌بندی‌ها در صفحه اصلی، فهرست خبر و منوی عمومی سایت استفاده می‌شوند و همگام‌سازی خبرها بر اساس همین دسته‌ها هوشمندانه مپ می‌شود.</p>

<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">افزودن دسته‌بندی</h2>
    <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.public.categories.store' : 'admin.public.categories.store') }}" class="grid grid-2">
        @csrf
        <div class="field"><label>عنوان</label><input class="input" name="title" required><div class="form-error" data-error-for="title"></div></div>
        <div class="field"><label>نامک / انگلیسی</label><input class="input" name="slug" dir="ltr" placeholder="world-cup-2026"><div class="form-error" data-error-for="slug"></div></div>
        <div class="field" style="grid-column:1/-1;"><label>توضیح</label><input class="input" name="description"><div class="form-error" data-error-for="description"></div></div>
        <div class="field"><label>ترتیب</label><input class="input" name="sort_order" inputmode="numeric" value="100"></div>
        <label class="toggle-row"><input type="checkbox" name="is_active" value="1" checked><span>فعال باشد</span></label>
        <button class="btn btn-primary" type="submit" style="grid-column:1/-1;">ثبت دسته‌بندی</button>
    </form>
</x-ui.card>

<div class="grid" style="margin-top:16px;">
@foreach($categories as $category)
    <x-ui.card>
        <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.public.categories.update' : 'admin.public.categories.update', $category) }}" class="grid grid-2">
            @csrf @method('PATCH')
            <div class="field"><label>عنوان</label><input class="input" name="title" value="{{ $category->title }}" required></div>
            <div class="field"><label>نامک</label><input class="input" dir="ltr" name="slug" value="{{ $category->slug }}" required></div>
            <div class="field" style="grid-column:1/-1;"><label>توضیح</label><input class="input" name="description" value="{{ $category->description }}"></div>
            <div class="field"><label>ترتیب</label><input class="input" name="sort_order" value="{{ $category->sort_order }}"></div>
            <label class="toggle-row"><input type="checkbox" name="is_active" value="1" @checked($category->is_active)><span>فعال</span></label>
            <button class="btn btn-outline" type="submit" style="grid-column:1/-1;">ذخیره تغییرات</button>
        </form>
    </x-ui.card>
@endforeach
</div>
@endsection
