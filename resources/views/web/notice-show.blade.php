@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
@endphp

@section('title', $notice->title.' | Notices | '.$hospitalName)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string) $notice->body) ?: $notice->title, 150))

@section('content')
    <x-web.page-intro
        title="Notice"
        note="Official desk update for patients and visitors."
    />

    <section class="ch-notices-page" id="notice-detail">
        <div class="ch-wrap">
            <p class="ch-notices-page__back">
                <a class="ch-more" href="{{ route('notices') }}">← All notices</a>
            </p>

            <article @class(['ch-notice', 'ch-notice--detail', 'ch-notice--urgent' => $notice->is_urgent, 'ch-notice--past' => $isPrevious])>
                <div class="ch-notice__meta">
                    <time class="ch-notice__date" datetime="{{ $notice->published_on->toDateString() }}">
                        {{ $notice->published_on->format('d M Y') }}
                    </time>
                    @if ($notice->is_urgent)
                        <span class="ch-notice__tag">Urgent</span>
                    @endif
                    @if ($isPrevious)
                        <span class="ch-notice__tag ch-notice__tag--past">Archived</span>
                    @endif
                </div>
                <div class="ch-notice__body">
                    <h1>{{ $notice->title }}</h1>
                    @if (filled($notice->body))
                        <p>{{ $notice->body }}</p>
                    @endif
                    @if ($isPrevious && $notice->expires_on)
                        <p class="ch-notice__ended">Ended {{ $notice->expires_on->format('d M Y') }}</p>
                    @endif
                </div>
            </article>
        </div>
    </section>
@endsection
