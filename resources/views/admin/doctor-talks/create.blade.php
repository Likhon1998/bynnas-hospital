@extends('layouts.admin')
@section('title', "Add Doctor's Talk")
@section('heading', "Add Doctor's Talk")
@section('content')
    @include('admin.doctor-talks._form', ['action' => route('admin.doctor-talks.store'), 'method' => 'POST'])
@endsection
