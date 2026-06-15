@extends('layouts.public')

@section('content')
<section class="pg-page-head"><span>ویدئو</span><h1>ویدئوهای فوتبالی</h1><p>ویدئوها، خلاصه‌ها و محتوای تصویری مرتبط با فوتبال و جام جهانی.</p></section>
<x-public.ad-slot name="videos_top" :slots="$adSlots" />
<div class="pg-news-grid pg-listing-grid">
    @forelse($articles as $article)
        <x-public.news-card :article="$article" />
    @empty
        <div class="pg-soft-card">فعلاً ویدئویی ثبت نشده است.</div>
    @endforelse
</div>
<div class="pg-pagination">{{ $articles->links() }}</div>
@endsection
