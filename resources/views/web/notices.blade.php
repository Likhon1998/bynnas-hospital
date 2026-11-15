@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
@endphp

@section('title', 'Notices | '.$hospitalName)
@section('meta_description', 'Current and previous hospital notices from '.$hospitalName.'.')

@section('content')
    <x-web.page-intro
        title="Notices"
        note="Official desk updates for patients and visitors."
    />

    <section class="ch-notices-page">
        <div class="ch-wrap">
            <div class="ch-notices-page__block">
                <h2 class="ch-notices-page__heading">Current notices</h2>
                <div class="ch-notices">
                    @forelse ($currentNotices as $notice)
                        <article @class(['ch-notice', 'ch-notice--urgent' => $notice->is_urgent])>
                            <time class="ch-notice__date" datetime="{{ $notice->published_on->toDateString() }}">
                                {{ $notice->published_on->format('d M Y') }}
                            </time>
                            <div class="ch-notice__body">
                                @if ($notice->is_urgent)
                                    <span class="ch-notice__tag">Urgent</span>
                                @endif
                                <h3>{{ $notice->title }}</h3>
                                @if (filled($notice->body))
                                    <p>{{ $notice->body }}</p>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="ch-lead">No current notices.</p>
                    @endforelse
                </div>
            </div>

            <div class="ch-notices-page__block" id="previous">
                <h2 class="ch-notices-page__heading">Previous notices</h2>
                <div class="ch-notices">
                    @forelse ($previousNotices as $notice)
                        <article class="ch-notice ch-notice--past">
                            <time class="ch-notice__date" datetime="{{ $notice->published_on->toDateString() }}">
                                {{ $notice->published_on->format('d M Y') }}
                            </time>
                            <div class="ch-notice__body">
                                <span class="ch-notice__tag ch-notice__tag--past">Archived</span>
                                <h3>{{ $notice->title }}</h3>
                                @if (filled($notice->body))
                                    <p>{{ $notice->body }}</p>
                                @endif
                                @if ($notice->expires_on)
                                    <p class="ch-notice__ended">Ended {{ $notice->expires_on->format('d M Y') }}</p>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="ch-lead">No previous notices yet.</p>
                    @endforelse
                </div>
                @if ($previousNotices->hasPages())
                    <div class="ch-notices-page__pager">{{ $previousNotices->links() }}</div>
                @endif
            </div>
        </div>
    </section>
@endsection
