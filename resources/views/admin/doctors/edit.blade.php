@extends('layouts.admin')
@section('title', 'Edit doctor')
@section('heading', 'Edit doctor')
@section('content')
    @can('appointments.view')
        <div class="mb-4">
            <a href="{{ route('admin.appointments.index', ['doctor' => $doctor->id]) }}" class="form-btn form-btn--navy">View appointments</a>
        </div>
    @endcan
    @include('admin.doctors._form', ['action' => route('admin.doctors.update', $doctor), 'method' => 'PUT'])
    @include('admin.doctors._clinic-hours')
@endsection
