@extends('layouts.admin')
@section('title', 'Add patient')
@section('heading', 'Add patient')
@section('content')
    <div class="patient-register">
        <section class="patient-register__hero">
            <div class="patient-register__intro">
                <p class="patient-register__eyebrow">Patient registration</p>
                <h2>Create a complete care record</h2>
                <p>
                    @if ($returnTo === 'beds')
                        Register the patient first, then return to the bed board to admit them to an available bed.
                    @else
                        Capture identity, demographics, allergies, and emergency contact so reception, nursing, lab, and billing share one accurate record.
                    @endif
                </p>
            </div>

            <ol class="patient-register__steps" aria-label="Registration steps">
                <li class="is-teal"><span>1</span>Identity</li>
                <li class="is-blue"><span>2</span>Demographics</li>
                <li class="is-rose"><span>3</span>Safety</li>
                <li class="is-amber"><span>4</span>Emergency</li>
                <li class="is-green"><span>5</span>Status</li>
            </ol>

            @if ($returnTo === 'beds')
                <div class="patient-register__callout">
                    <strong>Bed admission path</strong>
                    <span>After saving, you will return to available beds to finish allocation.</span>
                </div>
            @endif
        </section>

        <x-admin.form-page
            :action="route('admin.patients.store')"
            method="POST"
            :cancel="$returnTo === 'beds' ? route('admin.beds.index', ['status' => 'available']) : route('admin.patients.index')"
            submit="Register patient"
            class="patient-register__form"
        >
            @if ($returnTo === 'beds')
                <input type="hidden" name="return_to" value="beds">
            @endif

            @include('admin.patients._form-fields')
        </x-admin.form-page>
    </div>
@endsection
