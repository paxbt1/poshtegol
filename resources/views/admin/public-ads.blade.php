@php($newsAdmin = request()->routeIs('news-admin.*'))
@extends($newsAdmin ? 'layouts.news-admin' : 'layouts.admin')

@section('content')
<h1 class="title">مدیریت تبلیغات پشت گل</h1>
<p class="muted">جایگاه چسبان حذف شده است. برای تبلیغات می‌توانید متن، تصویر دسکتاپ، تصویر موبایل، دکمه و لینک تعریف کنید. مسیر تصویر می‌تواند آپلود شود یا به‌صورت دستی وارد شود.</p>

<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">افزودن تبلیغ</h2>
    <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.public.ads.store' : 'admin.public.ads.store') }}" enctype="multipart/form-data" class="grid grid-2">
        @csrf
        <div class="field"><label>جایگاه</label><select class="input" name="ad_slot_id">@foreach($slots as $slot)<option value="{{ $slot->id }}">{{ $slot->title }} / {{ $slot->key }}</option>@endforeach</select></div>
        <div class="field"><label>عنوان تبلیغ</label><input class="input" name="title" required></div>
        <div class="field" style="grid-column:1/-1;"><label>متن تبلیغ</label><textarea class="input" name="body_text" style="height:80px;padding-top:10px;"></textarea></div>
        <div class="field"><label>تصویر دسکتاپ / مسیر دستی</label><input class="input" name="image_desktop" dir="ltr"></div>
        <div class="field"><label>آپلود تصویر دسکتاپ</label><input class="input" type="file" name="image_desktop_file" accept="image/*"></div>
        <div class="field"><label>تصویر موبایل / مسیر دستی</label><input class="input" name="image_mobile" dir="ltr"></div>
        <div class="field"><label>آپلود تصویر موبایل</label><input class="input" type="file" name="image_mobile_file" accept="image/*"></div>
        <div class="field"><label>متن دکمه</label><input class="input" name="cta_text" value="مشاهده"></div>
        <div class="field"><label>لینک</label><input class="input" name="link_url" dir="ltr" placeholder="https://..."></div>
        <div class="field"><label>ترتیب</label><input class="input" name="sort_order" value="0"></div>
        <label class="toggle-row"><input type="checkbox" name="is_active" value="1" checked><span>فعال</span></label>
        <button class="btn btn-primary" type="submit" style="grid-column:1/-1;">ثبت تبلیغ</button>
    </form>
</x-ui.card>

@foreach($slots as $slot)
    <x-ui.card style="margin-top:16px;">
        <h2 class="section-title" style="margin-top:0;">{{ $slot->title }} <span class="muted small">{{ $slot->key }} / {{ $slot->width }}×{{ $slot->height }}</span></h2>
        <div class="grid">
            @forelse($slot->ads as $ad)
                <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.public.ads.update' : 'admin.public.ads.update', $ad) }}" enctype="multipart/form-data" class="admin-ad-row admin-ad-row-pro">
                    @csrf @method('PATCH')
                    @if($ad->image_desktop || $ad->image_mobile)<img class="admin-ad-preview" src="{{ $ad->image_desktop ?: $ad->image_mobile }}" alt="">@endif
                    <input class="input" name="title" value="{{ $ad->title }}" required placeholder="عنوان">
                    <textarea class="input" name="body_text" style="height:70px;padding-top:10px;" placeholder="متن تبلیغ">{{ $ad->body_text }}</textarea>
                    <input class="input" name="image_desktop" value="{{ $ad->image_desktop }}" dir="ltr" placeholder="تصویر دسکتاپ">
                    <input class="input" type="file" name="image_desktop_file" accept="image/*">
                    <input class="input" name="image_mobile" value="{{ $ad->image_mobile }}" dir="ltr" placeholder="تصویر موبایل">
                    <input class="input" type="file" name="image_mobile_file" accept="image/*">
                    <input class="input" name="cta_text" value="{{ $ad->cta_text }}" placeholder="متن دکمه">
                    <input class="input" name="link_url" value="{{ $ad->link_url }}" dir="ltr" placeholder="لینک">
                    <label class="toggle-row compact"><input type="checkbox" name="is_active" value="1" @checked($ad->is_active)><span>فعال</span></label>
                    <button class="btn btn-outline" type="submit">ذخیره</button>
                </form>
                <form data-ajax method="POST" action="{{ route(request()->routeIs('news-admin.*') ? 'news-admin.public.ads.destroy' : 'admin.public.ads.destroy', $ad) }}" onsubmit="return confirm('حذف شود؟')">
                    @csrf @method('DELETE')
                    <button class="btn btn-soft" type="submit">حذف تبلیغ {{ $ad->title }}</button>
                </form>
            @empty
                <p class="muted">برای این جایگاه هنوز تبلیغی ثبت نشده است؛ placeholder نمایشی نشان داده می‌شود.</p>
            @endforelse
        </div>
    </x-ui.card>
@endforeach
@endsection
