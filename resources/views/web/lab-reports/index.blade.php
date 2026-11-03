@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $deskPhone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
@endphp

@section('title', 'Lab reports | '.$hospitalName)
@section('meta_description', 'View and download your published laboratory reports with your phone number and payment access code.')

@section('content')
    <section class="ch-clinics-hero ch-lab-hero">
        <div class="ch-wrap">
            <p class="ch-find__kicker">Patients & visitors</p>
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>Lab reports</h1>
                    <p>Look up published results with the phone number on your record and the access code from your money receipt.</p>
                </div>
                <div class="ch-clinics-hero__actions">
                    <a class="ch-btn" href="{{ route('lab-reports.recover') }}">Forgot access code?</a>
                    @if ($deskPhone)
                        <a class="ch-btn ch-btn--ghost" href="tel:{{ preg_replace('/\s+/', '', $deskPhone) }}">Call desk</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="ch-lab">
        <div class="ch-wrap ch-lab__grid">
            <form class="ch-lab__form" method="POST" action="{{ route('lab-reports.lookup') }}">
                @csrf
                <p class="ch-find__kicker">Secure lookup</p>
                <h2 class="ch-sec__title">Find your report</h2>
                <p class="ch-lab__lead">Both details must match the payment that covered the test.</p>

                <label class="ch-lab__field">
                    <span>Phone number</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="20" autocomplete="tel" placeholder="Same number used at registration">
                    @error('phone')
                        <em>{{ $message }}</em>
                    @enderror
                </label>

                <label class="ch-lab__field">
                    <span>Report access code</span>
                    <input type="text" name="access_code" value="{{ old('access_code') }}" required maxlength="20" inputmode="numeric" autocomplete="off" placeholder="8-digit code from payment receipt">
                    @error('access_code')
                        <em>{{ $message }}</em>
                    @enderror
                </label>

                <button type="submit" class="ch-btn ch-btn--solid">View reports</button>
                <p class="ch-lab__help">
                    Lost the code?
                    <a href="{{ route('lab-reports.recover') }}">Recover with patient ID</a>
                </p>
            </form>

            <div class="ch-lab__aside">
                <p class="ch-find__kicker">Before you start</p>
                <h2 class="ch-sec__title">What you need</h2>
                <ul class="ch-lab__steps">
                    <li>
                        <strong>01</strong>
                        <span>Pay for the lab test at the billing desk and keep the receipt.</span>
                    </li>
                    <li>
                        <strong>02</strong>
                        <span>Note the 8-digit access code printed with the money receipt.</span>
                    </li>
                    <li>
                        <strong>03</strong>
                        <span>Return here after the laboratory publishes the verified report.</span>
                    </li>
                </ul>
                <p class="ch-lab__note">Unpublished or unverified results stay with the lab desk until they are released.</p>
            </div>
        </div>
    </section>
@endsection
