@extends('layouts.public')

@section('content')
<section class="pg-page-head">
    <span>آرشیو پشت گل</span>
    <h1>اخبار فوتبال و جام جهانی</h1>
    <p>تیترهای انگلیسی از منابع خبری دریافت، ترجمه و با تصویر محلی در پشت گل منتشر می‌شوند.</p>
</section>

<x-public.ad-slot name="category_top" :slots="$adSlots" />

<form class="pg-filterbar" method="GET" action="{{ route('public.news') }}">
    <input name="q" value="{{ $query }}" placeholder="جستجو در عنوان و خلاصه خبر...">
    <select name="category">
        <option value="">همه دسته‌بندی‌ها</option>
        @foreach($categories as $category)
            <option value="{{ $category->slug }}" @selected(($selectedCategory?->id ?? null) === $category->id)>{{ $category->title }}</option>
        @endforeach
    </select>
    <button type="submit">اعمال فیلتر</button>
</form>

<div class="pg-news-grid pg-listing-grid">
    @forelse($articles as $article)
        <x-public.news-card :article="$article" />
    @empty
        <div class="pg-soft-card">خبری با این فیلتر پیدا نشد.</div>
    @endforelse
</div>

<div class="pg-pagination">{{ $articles->links() }}</div>
@endsection
