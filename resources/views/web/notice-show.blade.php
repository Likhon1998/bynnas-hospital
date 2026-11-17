@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
@endphp

@section('title', $notice->title.' | Notices | '.$hospitalName)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string) $notice->body) ?: $notice->title, 150))

@section('content')
    <x-web.page-intro
        :title="$notice->title"
        note="Official desk update for patients and visitors."
    >
        <p class="mt-4">
            <a class="ch-more" href="{{ route('notices') }}">← All notices</a>
        </p>
    </x-web.page-intro>

    <section class="ch-notices-page" id="notice-detail">
        <div class="ch-wrap">
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
                    @if (filled($notice->body))
                        <p>{{ $notice->body }}</p>
                    @else
                        <p>{{ $notice->title }}</p>
                    @endif
                    @if ($isPrevious && $notice->expires_on)
                        <p class="ch-notice__ended">Ended {{ $notice->expires_on->format('d M Y') }}</p>
                    @endif
                </div>
            </article>
        </div>
    </section>
@endsection
