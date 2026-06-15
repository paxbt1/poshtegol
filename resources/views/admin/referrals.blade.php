@extends('layouts.admin')

@php
    $renderInviteTable = function ($links) {
        return $links;
    };
@endphp

@section('content')
<h1 class="title">مدیریت دعوت‌ها</h1>

<div class="grid desktop-grid-2" style="margin-top:16px;">
    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">ساخت لینک دسترسی مادر</h2>
        <form data-ajax method="POST" action="{{ route('admin.invite-links.store') }}">
            @csrf
            <input type="hidden" name="type" value="master_access">
            <div class="field"><label>عنوان</label><input class="input" name="title" value="لینک دسترسی مادر"></div>
            <div class="field"><label>حداکثر استفاده</label><input class="input" name="max_uses" inputmode="numeric"></div>
            <div class="field"><label>تاریخ انقضا</label><input class="input" name="expires_at" type="datetime-local"></div>
            <button class="btn btn-primary w-full" type="submit">ساخت لینک مادر</button>
        </form>
    </x-ui.card>

    <x-ui.card>
        <h2 class="section-title" style="margin-top:0;">ساخت لینک دعوت کاربر</h2>
        <form data-ajax method="POST" action="{{ route('admin.invite-links.store') }}">
            @csrf
            <input type="hidden" name="type" value="user_referral">
            <div class="field">
                <label>کاربر مالک لینک</label>
                <select class="input" name="owner_user_id" required>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->full_name }} - {{ $user->mobile }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label>عنوان</label><input class="input" name="title" value="لینک دعوت کاربر"></div>
            <div class="field"><label>حداکثر استفاده</label><input class="input" name="max_uses" inputmode="numeric"></div>
            <div class="field"><label>تاریخ انقضا</label><input class="input" name="expires_at" type="datetime-local"></div>
            <button class="btn btn-primary w-full" type="submit">ساخت لینک دعوت</button>
        </form>
    </x-ui.card>
</div>

@foreach([
    'لینک‌های دسترسی مادر' => $masterInviteLinks,
    'لینک‌های دعوت کاربران' => $userInviteLinks,
] as $title => $links)
    <x-ui.card style="margin-top:16px;">
        <h2 class="section-title" style="margin-top:0;">{{ $title }}</h2>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>کد</th>
                        <th>لینک کامل</th>
                        <th>نوع</th>
                        <th>مالک</th>
                        <th>استفاده</th>
                        <th>انقضا</th>
                        <th>فعال</th>
                        <th>کمیسیون</th>
                        <th>زمان ساخت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($links as $link)
                        <tr>
                            <td>{{ $link->title }}</td>
                            <td><code>{{ $link->code }}</code></td>
                            <td>
                                <button class="btn btn-soft" type="button" data-copy="{{ route('join', $link->code) }}">کپی لینک</button>
                            </td>
                            <td>{{ $link->typeLabel() }}</td>
                            <td>{{ $link->owner?->full_name ?? 'بدون مالک' }}</td>
                            <td>{{ $link->used_count }} / {{ $link->max_uses ?? 'نامحدود' }}</td>
                            <td>{{ $link->expires_at ? \App\Support\Jalali::format($link->expires_at, 'Y/m/d H:i') : 'بدون انقضا' }}</td>
                            <td>{{ $link->is_active ? 'فعال' : 'غیرفعال' }}</td>
                            <td>{{ $link->earns_commission ? 'دارد' : 'ندارد' }}</td>
                            <td>{{ \App\Support\Jalali::format($link->created_at, 'Y/m/d H:i') }}</td>
                            <td>
                                <form data-ajax method="POST" action="{{ route('admin.invite-links.update', $link) }}" style="display:inline-grid; gap:6px; min-width:160px;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="title" value="{{ $link->title }}">
                                    <input type="hidden" name="max_uses" value="{{ $link->max_uses }}">
                                    <input type="hidden" name="expires_at" value="{{ $link->expires_at?->format('Y-m-d H:i:s') }}">
                                    <label class="muted small"><input type="checkbox" name="is_active" value="1" @checked($link->is_active)> فعال باشد</label>
                                    <button class="btn btn-outline" type="submit">ذخیره</button>
                                </form>
                                @if($link->used_count === 0)
                                    <form data-ajax method="POST" action="{{ route('admin.invite-links.destroy', $link) }}" style="margin-top:6px;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline" type="submit">حذف</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11">لینکی ثبت نشده است.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
@endforeach

<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">رابطه‌های دعوت ثبت‌شده</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>دعوت‌کننده</th><th>عضو دعوت‌شده</th><th>منبع</th><th>فعال تا</th><th>زمان ثبت</th></tr></thead>
            <tbody>
                @forelse($referrals as $relation)
                    <tr>
                        <td>{{ $relation->inviter->full_name }}</td>
                        <td>{{ $relation->referred->full_name }}</td>
                        <td>لینک دعوت کاربر</td>
                        <td>{{ $relation->active_until ? \App\Support\Jalali::format($relation->active_until, 'Y/m/d') : 'پایان مرحله گروهی' }}</td>
                        <td>{{ \App\Support\Jalali::format($relation->created_at, 'Y/m/d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">دعوت موفقی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>
@endsection
