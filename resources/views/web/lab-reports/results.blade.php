@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
@endphp

@section('title', 'Your lab reports | '.$hospitalName)
@section('meta_description', 'Published laboratory reports linked to your payment.')

@section('content')
    <section class="ch-clinics-hero ch-lab-hero">
        <div class="ch-wrap">
            <p class="ch-find__kicker">Patients & visitors</p>
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>Your reports</h1>
                    <p>Bill {{ $bill->bill_no }} · verified for {{ $bill->patient?->full_name }}</p>
                </div>
                <div class="ch-clinics-hero__actions">
                    <a class="ch-btn" href="{{ route('lab-reports.index') }}">New lookup</a>
                </div>
            </div>
        </div>
    </section>

    <section class="ch-lab">
        <div class="ch-wrap">
            @if ($reports->isEmpty())
                <div class="ch-lab__empty">
                    <p class="ch-find__kicker">Not ready yet</p>
                    <h2 class="ch-sec__title">Payment matched</h2>
                    <p class="ch-lab__lead">No published report is ready for this payment. Check again after the laboratory finishes verification, or ask the lab desk.</p>
                    <a href="{{ route('lab-reports.index') }}" class="ch-btn ch-btn--solid">Look up again</a>
                </div>
            @else
                <div class="ch-lab__list">
                    @foreach ($reports as $order)
                        <article class="ch-lab__report">
                            <div>
                                <p class="ch-lab__report-meta">{{ $order->order_no }} · {{ $order->result?->published_at?->format('d M Y H:i') ?: 'Published' }}</p>
                                <h2>{{ $order->test?->name ?: 'Laboratory test' }}</h2>
                            </div>
                            <a href="{{ route('lab-reports.download', $order) }}" class="ch-btn ch-btn--solid">View / download</a>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
