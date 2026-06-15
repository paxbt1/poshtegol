@extends('layouts.auth')

@section('content')
@php($canRegister = $canRegister ?? false)
<section class="auth-card auth-card-wide">
    <span class="brand-pill">{{ $canRegister ? 'کاپ خانوادگی' : 'ورود مدیر' }}</span>
    <h1 class="title" style="margin-top:14px;">{{ $canRegister ? 'دعوت‌نامه معتبر است' : 'ورود به پنل مدیریت' }}</h1>
    <p class="muted" style="line-height:1.9;">
        @if($canRegister)
            برای ورود یا عضویت، اطلاعات حساب خود را وارد کنید. شماره کارت فقط برای تسویه جوایز در پایان دوره‌ها دریافت می‌شود.
        @else
            اگر مدیر سایت خبری یا کاپ هستید با شماره موبایل و رمز عبور تعریف‌شده در فایل .env وارد شوید. عضویت کاربران فقط با لینک دعوت مادر فعال می‌شود.
        @endif
    </p>

    @if($canRegister)
        <div class="auth-tabs" data-auth-tabs>
            <button class="auth-tab active" type="button" data-auth-tab="login">ورود</button>
            <button class="auth-tab" type="button" data-auth-tab="register">عضویت</button>
        </div>
    @endif

    <div data-auth-panel="login">
        <form data-ajax method="POST" action="{{ route('auth.login') }}">
            @csrf
            <div class="field">
                <label>شماره موبایل</label>
                <input class="input" name="mobile" inputmode="tel" autocomplete="username" placeholder="09123456789">
                <div class="form-error" data-error-for="mobile"></div>
            </div>
            <div class="field">
                <label>رمز عبور</label>
                <input class="input" name="password" type="password" autocomplete="current-password" placeholder="رمز عبور شما">
                <div class="form-error" data-error-for="password"></div>
            </div>
            <button class="btn btn-primary w-full" type="submit" style="margin-top:12px;">ورود</button>
        </form>
    </div>

    @if($canRegister)
        <div data-auth-panel="register" class="hidden">
            <form data-ajax method="POST" action="{{ route('auth.register') }}">
                @csrf
                <div class="grid grid-2">
                    <div class="field">
                        <label>نام</label>
                        <input class="input" name="first_name" autocomplete="given-name">
                        <div class="form-error" data-error-for="first_name"></div>
                    </div>
                    <div class="field">
                        <label>نام خانوادگی</label>
                        <input class="input" name="last_name" autocomplete="family-name">
                        <div class="form-error" data-error-for="last_name"></div>
                    </div>
                </div>
                <div class="field">
                    <label>شماره موبایل</label>
                    <input class="input" name="mobile" inputmode="tel" autocomplete="username" placeholder="09123456789">
                    <div class="form-error" data-error-for="mobile"></div>
                </div>
                <div class="field">
                    <label>شماره کارت</label>
                    <input class="input" name="card_number" inputmode="numeric" autocomplete="off" placeholder="۱۶ رقم">
                    <div class="form-error" data-error-for="card_number"></div>
                </div>
                <div class="grid grid-2">
                    <div class="field">
                        <label>رمز عبور</label>
                        <input class="input" name="password" type="password" autocomplete="new-password" placeholder="حداقل ۸ نویسه">
                        <div class="form-error" data-error-for="password"></div>
                    </div>
                    <div class="field">
                        <label>تکرار رمز عبور</label>
                        <input class="input" name="password_confirmation" type="password" autocomplete="new-password">
                        <div class="form-error" data-error-for="password_confirmation"></div>
                    </div>
                </div>
                <button class="btn btn-primary w-full" type="submit" style="margin-top:12px;">تکمیل عضویت</button>
            </form>
        </div>
    @endif
</section>
@endsection
