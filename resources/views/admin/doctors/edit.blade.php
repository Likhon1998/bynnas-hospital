@extends('layouts.admin')
@section('title', 'Edit doctor')
@section('heading', 'Edit doctor')
@section('content')
    @include('admin.doctors._form', ['action' => route('admin.doctors.update', $doctor), 'method' => 'PUT'])
@endsection
