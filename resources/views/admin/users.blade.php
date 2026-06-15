@extends('layouts.admin')

@section('content')
<h1 class="title">مدیریت کاربران</h1>
<p class="muted" style="margin-top:8px;line-height:1.8;">از این بخش می‌توانید وضعیت عضویت، نقش مدیر و رمز عبور کاربران را مدیریت کنید.</p>

<div class="grid" style="margin-top:16px;">
    @forelse($users as $user)
        <x-ui.card>
            <form data-ajax method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PATCH')
                <div class="admin-user-row">
                    <div>
                        <strong>{{ $user->full_name }}</strong>
                        <div class="muted small" style="margin-top:5px;">{{ $user->mobile }} · کارت: {{ $user->card_last4 ? '**** **** **** '.$user->card_last4 : 'ثبت نشده' }}</div>
                        <div class="muted small" style="margin-top:5px;">کد دعوت: {{ $user->invite_code }}</div>
                    </div>
                    <div class="admin-user-actions">
                        <span class="badge {{ $user->is_active ? 'badge-open' : 'badge-locked' }}">{{ $user->is_active ? 'فعال' : 'غیرفعال' }}</span>
                        <span class="badge {{ $user->is_admin ? 'badge-finished' : 'badge-locked' }}">{{ $user->is_admin ? 'مدیر' : 'عضو' }}</span>
                    </div>
                </div>

                <div class="grid desktop-grid-3" style="margin-top:14px;">
                    <div class="field">
                        <label>نام</label>
                        <input class="input" name="first_name" value="{{ $user->first_name }}" required>
                        <div class="form-error" data-error-for="first_name"></div>
                    </div>
                    <div class="field">
                        <label>نام خانوادگی</label>
                        <input class="input" name="last_name" value="{{ $user->last_name }}" required>
                        <div class="form-error" data-error-for="last_name"></div>
                    </div>
                    <div class="field">
                        <label>رمز عبور جدید</label>
                        <input class="input" name="password" type="password" placeholder="در صورت نیاز وارد کنید">
                        <div class="form-error" data-error-for="password"></div>
                    </div>
                </div>

                <div class="filters" style="margin-top:12px;align-items:center;">
                    <label class="toggle-row compact"><input type="checkbox" name="is_active" value="1" @checked($user->is_active)><span>حساب فعال باشد</span></label>
                    <label class="toggle-row compact"><input type="checkbox" name="is_admin" value="1" @checked($user->is_admin)><span>دسترسی مدیر</span></label>
                    <button class="btn btn-primary" type="submit">ذخیره کاربر</button>
                </div>
            </form>
        </x-ui.card>
    @empty
        <x-ui.card><p class="muted">هنوز کاربری ثبت نشده است.</p></x-ui.card>
    @endforelse
</div>
@endsection
