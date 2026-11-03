@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
@endphp

@section('title', 'Access codes recovered | '.$hospitalName)
@section('meta_description', 'Your laboratory report access codes.')

@section('content')
    <section class="ch-clinics-hero ch-lab-hero">
        <div class="ch-wrap">
            <p class="ch-find__kicker">Patients & visitors</p>
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>Access codes found</h1>
                    <p>Keep these with your receipts. Use any code with your phone number to open published reports.</p>
                </div>
                <div class="ch-clinics-hero__actions">
                    <a class="ch-btn ch-btn--solid" href="{{ route('lab-reports.index') }}">Open report lookup</a>
                </div>
            </div>
        </div>
    </section>

    <section class="ch-lab">
        <div class="ch-wrap ch-lab__narrow">
            <div class="ch-lab__form">
                <p class="ch-lab__report-meta">Patient {{ $patient->patient_code }} · {{ $patient->full_name }}</p>
                @if ($emailed)
                    <p class="ch-lab__flash" role="status">A copy was also sent to {{ $patient->email }}.</p>
                @else
                    <p class="ch-lab__lead">No email is on file, so the codes are shown here only. Ask the desk to add an email for next time.</p>
                @endif

                <ul class="ch-lab__codes">
                    @foreach ($codes as $row)
                        <li>
                            <p>Bill {{ $row->bill_no }} · {{ $row->bill_date }}</p>
                            <strong>{{ $row->report_access_code }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
@endsection
