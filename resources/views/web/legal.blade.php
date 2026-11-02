@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
@endphp

@section('title', $title.' | '.$hospitalName)
@section('meta_description', $note)

@section('content')
    <x-web.page-intro
        kicker="Hospital policy"
        :title="$title"
        :note="$note"
    />

    <section class="site-block">
        <div class="web-wrap">
            <div class="prose-service max-w-3xl text-[0.95rem] leading-7 text-[#475569]">
                {!! nl2br(e($body)) !!}
            </div>
        </div>
    </section>
@endsection
