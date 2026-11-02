@extends('layouts.admin')
@section('title', 'Clinic hours')
@section('heading', 'Clinic hours')
@section('content')
    <p class="form-lede">Change {{ $doctor->name }}'s clinic days and blocked dates. Patients see the new hours on the booking calendar immediately.</p>
    @include('admin.doctors._clinic-hours')
@endsection
