@extends('layouts.app')

@php
    $poolAmount = $participants->where('payment_status', 'paid')->sum('entry_amount');
    $homeName = $match->homeTeam?->name_fa ?? $match->bracket_slot_home ?? 'تیم میزبان';
    $awayName = $match->awayTeam?->name_fa ?? $match->bracket_slot_away ?? 'تیم مهمان';
    $homeFlag = $match->homeTeam?->flag_emoji ?? '🏆';
    $awayFlag = $match->awayTeam?->flag_emoji ?? '🏆';
    $homeCrest = $match->homeTeam?->crestDisplayUrl();
    $awayCrest = $match->awayTeam?->crestDisplayUrl();
    $startsIso = $match->starts_at?->timezone('Asia/Tehran')->toIso8601String();
    $resultLabels = [
        'home' => 'برد '.$homeName,
        'draw' => 'مساوی',
        'away' => 'برد '.$awayName,
    ];
    $totalGoalLabels = [
        'under_2_5' => 'کمتر از ۲.۵',
        'over_2_5' => 'بیشتر از ۲.۵',
    ];
@endphp

@section('content')
<section class="card hero-card" style="padding:22px;">
    <div style="position:relative; z-index:1;">
        <span class="brand-pill" style="background:rgba(255,255,255,.12); color:#fff;">{{ $match->stage_label_fa }} {{ $match->group_name ? ' - گروه '.$match->group_name : '' }}</span>
        <div class="teams" style="margin-top:22px;">
            <div class="team"><span class="flag team-logo">@if($homeCrest)<img src="{{ $homeCrest }}" alt="{{ $homeName }}">@else{{ $homeFlag }}@endif</span><span>{{ $homeName }}</span></div>
            <div class="versus" style="color:#fff;">VS</div>
            <div class="team"><span class="flag team-logo">@if($awayCrest)<img src="{{ $awayCrest }}" alt="{{ $awayName }}">@else{{ $awayFlag }}@endif</span><span>{{ $awayName }}</span></div>
        </div>
        <div class="countdown-box" data-countdown data-starts-at="{{ $startsIso }}"></div>
        <p style="color:rgba(255,255,255,.78); text-align:center;">شروع بازی: {{ \App\Support\Jalali::format($match->starts_at, 'Y/m/d H:i') }}</p>
        <p style="color:rgba(255,255,255,.78); text-align:center;">مهلت پیش‌بینی تا {{ \App\Support\Jalali::format($match->prediction_locks_at, 'Y/m/d H:i') }}</p>
    </div>
</section>

@if(! $canPredict)
    <div class="notice" style="margin-top:14px;">{{ $lockReason }}</div>
@endif

<div class="match-detail-layout" style="margin-top:14px;">
    <div class="match-main">
        @if($paidEntry)
            <x-ui.card>
                <h2 class="section-title" style="margin-top:0;">پیش‌بینی ثبت‌شده شما</h2>
                <div class="grid">
                    <div class="summary-row"><span>نتیجه نهایی</span><strong>{{ $resultLabels[$paidEntry->full_time_result] ?? '-' }}</strong></div>
                    <div class="summary-row"><span>نتیجه دقیق</span><strong>{{ $paidEntry->exact_home_score }} - {{ $paidEntry->exact_away_score }}</strong></div>
                    <div class="summary-row"><span>مجموع گل‌ها</span><strong>{{ $totalGoalLabels[$paidEntry->total_goals_option] ?? '-' }}</strong></div>
                    @if($paidEntry->qualifiedTeam)
                        <div class="summary-row"><span>تیم صعودکننده</span><strong>{{ $paidEntry->qualifiedTeam->name_fa }}</strong></div>
                    @endif
                    <div class="summary-row"><span>مبلغ ثبت‌شده برای این بازی</span><strong>{{ number_format($paidEntry->entry_amount) }} تومان</strong></div>
                </div>
            </x-ui.card>
        @else
            <x-ui.card>
                <h2 class="section-title" style="margin-top:0;">پیش‌بینی بازی</h2>
                <form data-ajax data-prediction-form data-preview-url="{{ route('matches.prediction.preview', $match) }}" method="POST" action="{{ route('matches.prediction.store', $match) }}">
                    @csrf
                    <div class="field">
                        <label>نتیجه نهایی</label>
                        <div class="segmented">
                            <label><input type="radio" name="full_time_result" value="home" checked @disabled(! $canPredict)><span class="segment">برد {{ $homeName }}</span></label>
                            <label><input type="radio" name="full_time_result" value="draw" @disabled(! $canPredict)><span class="segment">مساوی</span></label>
                            <label><input type="radio" name="full_time_result" value="away" @disabled(! $canPredict)><span class="segment">برد {{ $awayName }}</span></label>
                        </div>
                        <div class="form-error" data-error-for="full_time_result"></div>
                    </div>
                    <div class="score-grid">
                        <div class="field">
                            <label>گل {{ $homeName }}</label>
                            <select class="input score-select" name="exact_home_score" @disabled(! $canPredict)>@for($i=0;$i<=9;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor</select>
                            <div class="form-error" data-error-for="exact_home_score"></div>
                        </div>
                        <div class="field">
                            <label>گل {{ $awayName }}</label>
                            <select class="input score-select" name="exact_away_score" @disabled(! $canPredict)>@for($i=0;$i<=9;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor</select>
                            <div class="form-error" data-error-for="exact_away_score"></div>
                        </div>
                    </div>
                    <div class="field">
                        <label>مجموع گل‌ها</label>
                        <div class="segmented" style="grid-template-columns:repeat(2,minmax(0,1fr));">
                            <label><input type="radio" name="total_goals_option" value="under_2_5" checked @disabled(! $canPredict)><span class="segment">کمتر از ۲.۵</span></label>
                            <label><input type="radio" name="total_goals_option" value="over_2_5" @disabled(! $canPredict)><span class="segment">بیشتر از ۲.۵</span></label>
                        </div>
                        <div class="form-error" data-error-for="total_goals_option"></div>
                    </div>
                    <div class="field">
                        <label>تیم صعودکننده</label>
                        <select class="input" name="qualified_team_id" @disabled(! $canPredict || $match->stage === 'group' || $match->is_placeholder_match)>
                            @if($match->stage === 'group')
                                <option value="">برای مرحله گروهی غیرفعال است</option>
                            @elseif($match->is_placeholder_match)
                                <option value="">بعد از مشخص شدن تیم‌ها فعال می‌شود</option>
                            @else
                                <option value="{{ $match->home_team_id }}">{{ $homeName }}</option>
                                <option value="{{ $match->away_team_id }}">{{ $awayName }}</option>
                            @endif
                        </select>
                        <div class="form-error" data-error-for="qualified_team_id"></div>
                    </div>
                    <div class="form-error" data-error-for="match"></div>
                    <button class="btn btn-primary w-full" type="submit" @disabled(! $canPredict) style="margin-top:16px;">ثبت و پرداخت</button>
                </form>
            </x-ui.card>
        @endif
    </div>

    <aside class="match-aside">
        <x-ui.card>
            <h2 class="section-title" style="margin-top:0;">راهنمای امتیاز و صندوق</h2>
            <div class="guide-list">
                <div><strong>نتیجه نهایی</strong><span>اگر برد، مساوی یا باخت را درست انتخاب کنید، ۳ امتیاز می‌گیرید.</span></div>
                <div><strong>نتیجه دقیق</strong><span>اگر تعداد گل هر دو تیم دقیق باشد، ۵ امتیاز اضافه می‌شود.</span></div>
                <div><strong>مجموع گل‌ها</strong><span>کمتر از ۳ گل یعنی کمتر از ۲.۵؛ سه گل یا بیشتر یعنی بیشتر از ۲.۵ و پاسخ درست ۲ امتیاز دارد.</span></div>
                @if($match->stage !== 'group')
                    <div><strong>تیم صعودکننده</strong><span>در بازی‌های حذفی، انتخاب درست تیم صعودکننده ۳ امتیاز دارد.</span></div>
                @endif
                <div><strong>تقسیم مبلغ</strong><span>مبلغ شرکت هر بازی وارد صندوق همان دوره می‌شود. جایزه بر اساس رتبه نهایی دوره و قوانین تسویه بین نفرات برتر تقسیم می‌شود.</span></div>
            </div>
            <div class="summary-row" style="margin-top:8px;"><span>صندوق فعلی این بازی</span><strong>{{ number_format($poolAmount) }} تومان</strong></div>
        </x-ui.card>

        @unless($paidEntry)
            <x-ui.card>
                <h2 class="section-title" style="margin-top:0;">خلاصه پرداخت</h2>
                <div class="grid">
                    <div class="summary-row"><span>مبلغ قابل پرداخت</span><strong class="summary-total" data-payable-amount>{{ number_format($amounts['payable_amount']) }} تومان</strong></div>
                </div>
                <form data-ajax data-pay-form method="POST" action="#" class="hidden">
                    @csrf
                    <button class="btn btn-primary w-full" data-pay-button type="submit" disabled style="margin-top:18px;">رفتن به درگاه پرداخت</button>
                </form>
                <p class="muted small" style="line-height:1.8;">پس از پرداخت، مبلغ شرکت در بازی برای شما ثبت می‌شود و پیش‌بینی دیگر قابل تغییر نیست.</p>
            </x-ui.card>
        @endunless
    </aside>
</div>

<x-ui.card style="margin-top:14px;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <h2 class="section-title" style="margin:0;">شرکت‌کننده‌های این بازی</h2>
        <span class="chip active">{{ $participants->count() }} نفر</span>
    </div>
    @if($participants->isEmpty())
        <p class="muted" style="line-height:1.9;">هنوز کسی برای این بازی پیش‌بینی پرداخت‌شده ثبت نکرده است.</p>
    @else
        <div class="participants-list">
            @foreach($participants as $entry)
                <div class="participant-row">
                    <div>
                        <strong>{{ $entry->user->full_name }}</strong>
                        <span>{{ $entry->paid_at ? \App\Support\Jalali::format($entry->paid_at, 'Y/m/d H:i') : 'در انتظار بررسی' }}</span>
                    </div>
                    <div class="participant-meta">
                        @if($entry->result)
                            <span class="badge badge-open">{{ $entry->result->total_points }} امتیاز</span>
                        @else
                            <span class="badge badge-locked">ثبت‌شده</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-ui.card>
@endsection
