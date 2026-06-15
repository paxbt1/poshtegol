@extends('layouts.public')

@section('content')
<section class="pg-page-head">
    <span>دسته‌بندی</span>
    <h1>{{ $category->title }}</h1>
    <p>{{ $category->description ?: 'آخرین خبرهای این بخش را اینجا بخوانید.' }}</p>
</section>

<x-public.ad-slot name="category_top" :slots="$adSlots" />

<div class="pg-news-grid pg-listing-grid">
    @forelse($articles as $article)
        <x-public.news-card :article="$article" />
    @empty
        <div class="pg-soft-card">برای این دسته‌بندی هنوز خبری منتشر نشده است.</div>
    @endforelse
</div>
<div class="pg-pagination">{{ $articles->links() }}</div>
@endsection
