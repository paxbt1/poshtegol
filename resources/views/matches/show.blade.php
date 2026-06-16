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
    $isFinished = in_array($match->status, ['finished', 'awarded', 'after_extra_time', 'after_penalties', 'settled'], true);
    $hasScore = $match->home_score !== null && $match->away_score !== null;
    $resultLabels = ['home' => 'برد '.$homeName, 'draw' => 'مساوی', 'away' => 'برد '.$awayName];
    $totalGoalLabels = ['under_2_5' => 'کمتر از ۲.۵', 'over_2_5' => 'بیشتر از ۲.۵'];
@endphp

@section('content')
<section class="card hero-card" style="padding:22px;">
    <div style="position:relative; z-index:1;">
        <span class="brand-pill" style="background:rgba(255,255,255,.12); color:#fff;">{{ $match->stage_label_fa }} {{ $match->group_name ? ' - گروه '.$match->group_name : '' }}</span>
        <div class="teams" style="margin-top:22px;">
            <div class="team"><span class="flag team-logo">@if($homeCrest)<img src="{{ $homeCrest }}" alt="{{ $homeName }}">@else{{ $homeFlag }}@endif</span><span>{{ $homeName }}</span></div>
            <div class="{{ $isFinished && $hasScore ? 'final-score-pill light' : 'versus' }}" style="color:#fff;">{{ $isFinished && $hasScore ? $match->home_score.' - '.$match->away_score : 'VS' }}</div>
            <div class="team"><span class="flag team-logo">@if($awayCrest)<img src="{{ $awayCrest }}" alt="{{ $awayName }}">@else{{ $awayFlag }}@endif</span><span>{{ $awayName }}</span></div>
        </div>
        @if($isFinished && $hasScore)
            <div class="match-result-line light">نتیجه نهایی: {{ $homeName }} {{ $match->home_score }} - {{ $match->away_score }} {{ $awayName }}</div>
        @else
            <div class="countdown-box" data-countdown data-starts-at="{{ $startsIso }}"></div>
        @endif
        <p style="color:rgba(255,255,255,.78); text-align:center;">شروع بازی: {{ \App\Support\Jalali::format($match->starts_at, 'Y/m/d H:i') }}</p>
        <p style="color:rgba(255,255,255,.78); text-align:center;">مهلت پیش‌بینی تا {{ \App\Support\Jalali::format(app(\App\Services\MatchLockService::class)->lockTime($match), 'Y/m/d H:i') }}</p>
    </div>
</section>

@if(! $canPredict && ! $paidEntry)
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
                    <div class="summary-row"><span>توکن شرط</span><strong>{{ number_format($paidEntry->entry_amount) }} توکن</strong></div>
                    <div class="summary-row"><span>وضعیت</span><strong>ثبت و قفل‌شده</strong></div>
                </div>
            </x-ui.card>
        @elseif($canPredict)
            <x-ui.card>
                <h2 class="section-title" style="margin-top:0;">پیش‌بینی بازی</h2>
                <form data-ajax data-prediction-form data-preview-url="{{ route('matches.prediction.preview', $match) }}" method="POST" action="{{ route('matches.prediction.store', $match) }}">
                    @csrf
                    <div class="field">
                        <label>نتیجه نهایی</label>
                        <div class="segmented">
                            <label><input type="radio" name="full_time_result" value="home" checked><span class="segment">برد {{ $homeName }}</span></label>
                            <label><input type="radio" name="full_time_result" value="draw"><span class="segment">مساوی</span></label>
                            <label><input type="radio" name="full_time_result" value="away"><span class="segment">برد {{ $awayName }}</span></label>
                        </div>
                        <div class="form-error" data-error-for="full_time_result"></div>
                    </div>
                    <div class="score-grid">
                        <div class="field"><label>گل {{ $homeName }}</label><select class="input score-select" name="exact_home_score">@for($i=0;$i<=9;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor</select><div class="form-error" data-error-for="exact_home_score"></div></div>
                        <div class="field"><label>گل {{ $awayName }}</label><select class="input score-select" name="exact_away_score">@for($i=0;$i<=9;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor</select><div class="form-error" data-error-for="exact_away_score"></div></div>
                    </div>
                    <div class="field">
                        <label>مجموع گل‌ها</label>
                        <div class="segmented" style="grid-template-columns:repeat(2,minmax(0,1fr));">
                            <label><input type="radio" name="total_goals_option" value="under_2_5" checked><span class="segment">کمتر از ۲.۵</span></label>
                            <label><input type="radio" name="total_goals_option" value="over_2_5"><span class="segment">بیشتر از ۲.۵</span></label>
                        </div>
                        <div class="form-error" data-error-for="total_goals_option"></div>
                    </div>
                    <div class="field">
                        <label>تیم صعودکننده</label>
                        <select class="input" name="qualified_team_id" @disabled($match->stage === 'group' || $match->is_placeholder_match)>
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
                    <div class="token-stake-panel">
                        <div>
                            <label for="stake_tokens">تعداد توکن شرط</label>
                            <p class="muted small">حداقل شرط ۵۰ توکن است. هر توکن برابر ۱۰۰۰ تومان است و در پایان جام مبنای بدهکاری یا بستانکاری قرار می‌گیرد.</p>
                        </div>
                        <div class="token-input-wrap">
                            <input id="stake_tokens" class="input" name="stake_tokens" type="number" inputmode="numeric" min="50" max="100000" value="50">
                            <span>توکن</span>
                        </div>
                        <div class="summary-row token-summary"><span>ثبت نهایی</span><strong data-payable-amount>۵۰ توکن</strong></div>
                        <div class="form-error" data-error-for="stake_tokens"></div>
                    </div>
                    <div class="form-error" data-error-for="match"></div>
                    <button class="btn btn-primary w-full" type="submit" style="margin-top:16px;">ثبت شرط با توکن</button>
                </form>
            </x-ui.card>
        @endif
    </div>

    <aside class="match-aside">
        <x-ui.card>
            <h2 class="section-title" style="margin-top:0;">راهنمای امتیاز و صندوق</h2>
            <div class="guide-list">
                <div><strong>نتیجه نهایی</strong><span>انتخاب درست برد، مساوی یا باخت ۳ امتیاز دارد.</span></div>
                <div><strong>نتیجه دقیق</strong><span>اگر تعداد گل هر دو تیم دقیق باشد، ۵ امتیاز اضافه می‌شود.</span></div>
                <div><strong>مجموع گل‌ها</strong><span>کمتر از ۳ گل یعنی کمتر از ۲.۵ و سه گل یا بیشتر یعنی بیشتر از ۲.۵؛ پاسخ درست ۲ امتیاز دارد.</span></div>
                @if($match->stage !== 'group')
                    <div><strong>تیم صعودکننده</strong><span>در بازی‌های حذفی، انتخاب درست تیم صعودکننده ۳ امتیاز دارد.</span></div>
                @endif
            </div>
            <div class="summary-row" style="margin-top:8px;"><span>صندوق توکنی این بازی</span><strong>{{ number_format($poolAmount) }} توکن</strong></div>
        </x-ui.card>
    </aside>
</div>

<x-ui.card style="margin-top:14px;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <h2 class="section-title" style="margin:0;">شرکت‌کننده‌های این بازی</h2>
        <span class="chip active">{{ $participants->count() }} نفر</span>
    </div>
    @if($participants->isEmpty())
        <p class="muted" style="line-height:1.9;">هنوز کسی برای این بازی پیش‌بینی ثبت نکرده است.</p>
    @else
        <div class="participants-list">
            @foreach($participants as $entry)
                <div class="participant-row">
                    <div><strong>{{ $entry->user->full_name }}</strong><span>{{ $entry->paid_at ? \App\Support\Jalali::format($entry->paid_at, 'Y/m/d H:i') : 'ثبت‌شده' }}</span></div>
                    <div class="participant-meta">
                        @if($entry->result)
                            <span class="badge badge-open">{{ $entry->result->total_points }} امتیاز</span>
                        @else
                            <span class="badge badge-locked">{{ number_format($entry->entry_amount) }} توکن</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-ui.card>
@endsection
