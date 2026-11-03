@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $deskPhone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
@endphp

@section('title', 'Recover report access code | '.$hospitalName)
@section('meta_description', 'Recover your laboratory report access code with your phone number and patient ID.')

@section('content')
    <section class="ch-clinics-hero ch-lab-hero">
        <div class="ch-wrap">
            <p class="ch-find__kicker">Patients & visitors</p>
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>Recover access code</h1>
                    <p>Use the phone number on your record and the patient ID from your registration slip.</p>
                </div>
                <div class="ch-clinics-hero__actions">
                    <a class="ch-btn ch-btn--solid" href="{{ route('lab-reports.index') }}">Back to lookup</a>
                    @if ($deskPhone)
                        <a class="ch-btn" href="tel:{{ preg_replace('/\s+/', '', $deskPhone) }}">Call desk</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="ch-lab">
        <div class="ch-wrap ch-lab__narrow">
            <form class="ch-lab__form" method="POST" action="{{ route('lab-reports.recover.submit') }}">
                @csrf
                <p class="ch-find__kicker">Code recovery</p>
                <h2 class="ch-sec__title">Find your codes</h2>
                <p class="ch-lab__lead">Matched codes appear on screen. If an email is on file, a copy is sent there too.</p>

                <label class="ch-lab__field">
                    <span>Phone number</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="20" autocomplete="tel">
                    @error('phone')
                        <em>{{ $message }}</em>
                    @enderror
                </label>

                <label class="ch-lab__field">
                    <span>Patient ID</span>
                    <input type="text" name="patient_code" value="{{ old('patient_code') }}" required maxlength="40" autocomplete="off" placeholder="Printed on your patient slip">
                    @error('patient_code')
                        <em>{{ $message }}</em>
                    @enderror
                </label>

                <button type="submit" class="ch-btn ch-btn--solid">Recover codes</button>
                <p class="ch-lab__help">
                    Still stuck? Call the hospital desk with your name and test date.
                </p>
            </form>
        </div>
    </section>
@endsection
