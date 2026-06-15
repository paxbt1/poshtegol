@props(['name', 'slots' => collect(), 'class' => ''])
@php
    $slot = $slots instanceof \Illuminate\Support\Collection ? $slots->get($name) : null;
    $ads = $slot?->activeAds ?? collect();
    $ad = $ads->first();
    $enabled = \App\Models\AppSetting::getBool('public_ads_enabled', true);
@endphp
@if($enabled && $name !== 'mobile_sticky_bottom')
    <div class="pg-ad-slot pg-ad-{{ $name }} {{ $class }}" data-slot="{{ $name }}">
        @if($ad && ($ad->image_desktop || $ad->image_mobile || $ad->body_text))
            <a class="pg-ad-link" href="{{ $ad->link_url ?: '#' }}" @if($ad->target_blank) target="_blank" @endif rel="{{ trim(($ad->rel_nofollow ? 'nofollow ' : '').($ad->rel_sponsored ? 'sponsored' : '')) }}">
                @if($ad->image_desktop || $ad->image_mobile)
                    <picture>
                        @if($ad->image_mobile)<source media="(max-width: 680px)" srcset="{{ $ad->image_mobile }}">@endif
                        <img src="{{ $ad->image_desktop ?: $ad->image_mobile }}" alt="{{ $ad->title }}" loading="lazy">
                    </picture>
                @endif
                @if($ad->body_text || $ad->title)
                    <span class="pg-ad-copy">
                        <strong>{{ $ad->title }}</strong>
                        @if($ad->body_text)<em>{{ $ad->body_text }}</em>@endif
                        @if($ad->cta_text)<b>{{ $ad->cta_text }}</b>@endif
                    </span>
                @endif
            </a>
        @else
            <div class="pg-ad-placeholder">
                <span>جایگاه تبلیغاتی</span>
                <strong>{{ $slot?->title ?? 'تبلیغات پشت گل' }}</strong>
                <em>{{ $slot?->width ? $slot->width.'×'.$slot->height : 'قابل تنظیم از پنل مدیریت' }}</em>
            </div>
        @endif
    </div>
@endif
