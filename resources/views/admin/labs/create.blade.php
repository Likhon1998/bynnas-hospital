@extends('layouts.admin')
@section('title', 'Order lab test')
@section('heading', 'Order lab test')
@section('content')
    <x-admin.form-page
        :action="route('admin.labs.store')"
        method="POST"
        :cancel="route('admin.labs.index')"
        submit="Place order"
        lede="Orders stay on the clinical encounter. The test price is added to the patient bill once."
    >
        <x-admin.form-section title="Order" note="Choose the encounter and the catalog test.">
            <x-form.select name="visit_id" label="Encounter" required>
                <option value="">Select encounter</option>
                @foreach ($visits as $option)
                    <option value="{{ $option->id }}" @selected((string) old('visit_id', $visit?->id) === (string) $option->id)>
                        {{ $option->visit_no }} · {{ $option->patient?->full_name }}
                    </option>
                @endforeach
            </x-form.select>
            <x-form.select name="lab_test_id" label="Test" required>
                <option value="">Select test</option>
                @foreach ($tests as $test)
                    <option value="{{ $test->id }}" @selected((string) old('lab_test_id') === (string) $test->id)>
                        {{ $test->name }} · {{ $test->code }} · {{ number_format((float) $test->price, 2) }}
                    </option>
                @endforeach
            </x-form.select>
            <div class="form-span-2">
                <x-form.input name="clinical_notes" label="Clinical notes" :value="old('clinical_notes', $visit?->chief_complaint)" />
            </div>
        </x-admin.form-section>
    </x-admin.form-page>
@endsection
