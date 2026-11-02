@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
@endphp

@section('title', 'News & Updates | '.$hospitalName)
@section('meta_description', 'Campus news and health updates from '.$hospitalName.'.')

@section('content')
    <x-web.page-intro
        kicker="Campus updates"
        title="News & Updates"
        note="Articles the desk publishes for families. This is general information, not a personal medical plan."
    />

    <section class="site-block">
        <div class="web-wrap">
            <div class="ch-news">
                @forelse ($posts as $post)
                    <article class="ch-article">
                        <img src="{{ \App\Support\PublicPhoto::article($post->cover_image, $loop->index) }}" alt="{{ $post->title }}">
                        @if ($post->published_at)
                            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time>
                        @endif
                        <h3>{{ $post->title }}</h3>
                        <p>{{ $post->excerpt }}</p>
                        <a class="ch-more" href="{{ route('news.show', $post) }}">Read more →</a>
                    </article>
                @empty
                    <p class="ch-lead">Campus updates will appear here when the desk publishes them.</p>
                @endforelse
            </div>
            <div class="mt-10">{{ $posts->links() }}</div>
        </div>
    </section>
@endsection
