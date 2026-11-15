@extends('layouts.admin')
@section('title', "Edit Doctor's Talk")
@section('heading', "Edit Doctor's Talk")
@section('content')
    @include('admin.doctor-talks._form', ['action' => route('admin.doctor-talks.update', $talk), 'method' => 'PUT'])
@endsection
