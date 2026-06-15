@php($current = request()->route()?->getName())
<nav class="bottom-nav">
    <a class="{{ $current === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}"><span>خانه</span><span>داشبورد</span></a>
    <a class="{{ str_starts_with($current ?? '', 'matches') ? 'active' : '' }}" href="{{ route('matches.index') }}"><span>بازی</span><span>بازی‌ها</span></a>
    <a class="{{ $current === 'ranking' ? 'active' : '' }}" href="{{ route('ranking') }}"><span>رتبه</span><span>جدول</span></a>
    <a class="{{ $current === 'invite' ? 'active' : '' }}" href="{{ route('invite') }}"><span>دعوت</span><span>لینک من</span></a>
    <a class="{{ $current === 'settlements' ? 'active' : '' }}" href="{{ route('settlements') }}"><span>تسویه</span><span>وضعیت</span></a>
</nav>
