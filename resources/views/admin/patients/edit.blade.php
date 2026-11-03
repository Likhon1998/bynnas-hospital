@extends('layouts.admin')
@section('title', 'Edit patient')
@section('heading', 'Edit patient')
@section('content')
    <div class="patient-register">
        <section class="patient-register__hero">
            <div class="patient-register__intro">
                <p class="patient-register__eyebrow">{{ $patient->patient_code ?: 'Patient record' }}</p>
                <h2>Update {{ $patient->full_name }}</h2>
                <p>Keep demographics, allergies, and emergency contact accurate so every desk uses the same chart.</p>
            </div>
        </section>

        <x-admin.form-page
            :action="route('admin.patients.update', $patient)"
            method="PUT"
            :cancel="route('admin.patients.show', $patient)"
            submit="Save patient"
            class="patient-register__form"
        >
            @include('admin.patients._form-fields', ['patient' => $patient])
        </x-admin.form-page>
    </div>
@endsection
