@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $phone = $hospital->get('emergency_phone') ?: $hospital->get('phone');
    $content = $hospital->get('emergency_content')
        ?: 'The emergency desk is open day and night. Come directly to the hospital if it cannot wait.';
@endphp

@section('title', 'Emergency desk | '.$hospitalName)
@section('meta_description', '24/7 emergency desk at '.$hospitalName.'. Call or come in if it cannot wait.')

@section('content')
    <x-web.page-intro
        kicker="Urgent care"
        title="Emergency desk"
        note="This desk stays open. Do not wait for clinic hours if breathing, chest pain, stroke signs, or heavy bleeding are present."
    />

    <section class="site-block">
        <div class="web-wrap grid items-start gap-10 lg:grid-cols-[1.35fr_0.65fr] lg:gap-16">
            <div class="min-w-0">
                <div class="site-section-head">
                    <h2>Come in now</h2>
                </div>
                <div class="prose-service text-[0.95rem] leading-7 text-[#475569]">
                    <p>{{ $content }}</p>
                    <p>Walk to the emergency entrance and tell the desk what happened. Staff will triage, then move the patient to an ICU, isolation, or general bed if admission is needed.</p>
                </div>
                <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-3">
                    @if ($phone)
                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="site-btn site-btn--green">Call {{ $phone }}</a>
                    @endif
                    @if ($service)
                        <a href="{{ route('services.show', $service) }}" class="text-sm font-semibold text-[#16a34a]">Emergency care service</a>
                    @endif
                </div>
            </div>
            <aside class="rounded-3xl bg-[#0f172a] p-6 text-white">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#86efac]">Open now</p>
                <p class="mt-2 text-2xl font-semibold">24/7 emergency</p>
                <p class="mt-3 text-sm leading-6 text-slate-300">Friday clinic may be closed. The emergency desk is not.</p>
            </aside>
        </div>
    </section>
@endsection
