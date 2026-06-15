@extends('layouts.public')

@section('content')
<section class="pg-page-head"><span>جستجو</span><h1>جستجو در پشت گل</h1><p>نتایج جستجو برای: {{ $query ?: 'همه خبرها' }}</p></section>
<form class="pg-filterbar" method="GET" action="{{ route('public.search') }}"><input name="q" value="{{ $query }}" placeholder="عبارت جستجو"><button>جستجو</button></form>
<div class="pg-news-grid pg-listing-grid">@forelse($articles as $article)<x-public.news-card :article="$article" />@empty<div class="pg-soft-card">نتیجه‌ای پیدا نشد.</div>@endforelse</div>
<div class="pg-pagination">{{ $articles->links() }}</div>
@endsection
