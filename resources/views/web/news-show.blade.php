@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
@endphp

@section('title', $post->title.' | '.$hospitalName)
@section('meta_description', $post->excerpt ?: $post->title)

@section('content')
    <x-web.page-intro
        :kicker="$post->blogCategory?->name ?: 'Campus update'"
        :title="$post->title"
        :note="$post->excerpt"
    />

    <section class="site-block">
        <div class="web-wrap grid items-start gap-10 lg:grid-cols-[1.35fr_0.65fr] lg:gap-16">
            <div class="min-w-0">
                <img src="{{ \App\Support\PublicPhoto::article($post->cover_image) }}" alt="{{ $post->title }}" class="mb-8 w-full rounded-[1.35rem] object-cover">
                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toDateString() }}" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#64748b]">{{ $post->published_at->format('F j, Y') }}</time>
                @endif
                <div class="prose-service mt-4 text-[0.95rem] leading-7 text-[#475569]">
                    {!! $post->body !!}
                </div>
                <div class="mt-8">
                    <a href="{{ route('news') }}" class="text-sm font-semibold text-[#16a34a]">All news</a>
                </div>
            </div>
            @if ($related->isNotEmpty())
                <aside>
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#16a34a]">More from the desk</p>
                    <div class="mt-4 grid gap-4">
                        @foreach ($related as $item)
                            <a href="{{ route('news.show', $item) }}" class="block rounded-[1.2rem] border border-[#e2e8f0] bg-white p-4">
                                <strong class="text-[#122447]">{{ $item->title }}</strong>
                                <p class="mt-1 text-sm text-[#64748b]">{{ $item->excerpt }}</p>
                            </a>
                        @endforeach
                    </div>
                </aside>
            @endif
        </div>
    </section>
@endsection
