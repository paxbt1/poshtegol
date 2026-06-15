<header class="top-nav">
    <a class="brand" href="{{ route('dashboard') }}">
        <span class="brand-mark">ک</span>
        <span>کاپ خانوادگی</span>
    </a>
    @php($current = request()->route()?->getName())
    <nav class="desktop-nav">
        <a class="{{ $current === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}">داشبورد</a>
        <a class="{{ str_starts_with($current ?? '', 'matches') ? 'active' : '' }}" href="{{ route('matches.index') }}">بازی‌ها</a>
        <a class="{{ $current === 'ranking' ? 'active' : '' }}" href="{{ route('ranking') }}">رتبه‌بندی</a>
        <a class="{{ $current === 'settlements' ? 'active' : '' }}" href="{{ route('settlements') }}">تسویه‌ها</a>
        <a class="{{ $current === 'invite' ? 'active' : '' }}" href="{{ route('invite') }}">دعوت</a>
    </nav>
    <div class="nav-actions">
        @auth
            @if(auth()->user()->is_admin)
                <a class="btn btn-outline" href="{{ route('admin.index') }}">مدیریت</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-soft" type="submit">خروج</button>
            </form>
        @endauth
    </div>
</header>
