@extends('layouts.admin')

@section('content')
<h1 class="title">تنظیمات و گزارش توکنی</h1>
<div class="grid desktop-grid-4" style="margin-top:16px;">
    <x-stat-card label="کارمزد درگاه" :value="number_format($gatewayFees)" hint="حذف‌شده" />
    <x-stat-card label="صندوق توکنی" :value="number_format($poolAmount)" hint="توکن" />
    <x-stat-card label="تعهد کاربران" :value="number_format($paidAmount)" hint="توکن" />
    <x-stat-card label="نیازمند بررسی" :value="$needsReviewCount" hint="پرونده" />
</div>
<x-ui.card style="margin-top:16px;">
    <h2 class="section-title" style="margin-top:0;">تنظیمات توکن هر مرحله</h2>
    <form data-ajax method="POST" action="{{ route('admin.settings.finance.update') }}">
        @csrf
        <input type="hidden" name="gateway_fee_percent" value="{{ $settings['gateway_fee_percent'] }}">
        <div class="grid desktop-grid-3">
            <div class="field"><label>درصد پاداش دعوت</label><input class="input" name="referral_rate" value="{{ $settings['referral_rate'] }}"><div class="form-error" data-error-for="referral_rate"></div></div>
            <div class="field"><label>دعوت تا پایان گروهی</label><select class="input" name="referral_enabled_until_group_stage"><option value="1" @selected($settings['referral_enabled_until_group_stage'])>فعال</option><option value="0" @selected(! $settings['referral_enabled_until_group_stage'])>غیرفعال</option></select></div>
            <div class="field"><label>توکن پیش‌فرض مرحله گروهی</label><input class="input" name="group_entry_amount" value="{{ $settings['group_entry_amount'] }}"><div class="form-error" data-error-for="group_entry_amount"></div></div>
            <div class="field"><label>توکن یک‌شانزدهم نهایی</label><input class="input" name="round32_entry_amount" value="{{ $settings['round32_entry_amount'] }}"><div class="form-error" data-error-for="round32_entry_amount"></div></div>
            <div class="field"><label>توکن یک‌هشتم نهایی</label><input class="input" name="round16_entry_amount" value="{{ $settings['round16_entry_amount'] }}"><div class="form-error" data-error-for="round16_entry_amount"></div></div>
            <div class="field"><label>توکن یک‌چهارم نهایی</label><input class="input" name="quarter_final_entry_amount" value="{{ $settings['quarter_final_entry_amount'] }}"><div class="form-error" data-error-for="quarter_final_entry_amount"></div></div>
            <div class="field"><label>توکن نیمه‌نهایی</label><input class="input" name="semi_final_entry_amount" value="{{ $settings['semi_final_entry_amount'] }}"><div class="form-error" data-error-for="semi_final_entry_amount"></div></div>
            <div class="field"><label>توکن رده‌بندی</label><input class="input" name="bronze_final_entry_amount" value="{{ $settings['bronze_final_entry_amount'] }}"><div class="form-error" data-error-for="bronze_final_entry_amount"></div></div>
            <div class="field"><label>توکن فینال</label><input class="input" name="final_entry_amount" value="{{ $settings['final_entry_amount'] }}"><div class="form-error" data-error-for="final_entry_amount"></div></div>
        </div>
        <button class="btn btn-primary" type="submit" style="margin-top:16px;">ذخیره تنظیمات</button>
    </form>
</x-ui.card>
@endsection
