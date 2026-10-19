@extends('layouts.admin')
@section('title', 'Add doctor')
@section('heading', 'Add doctor')
@section('content')
    @include('admin.doctors._form', ['action' => route('admin.doctors.store'), 'method' => 'POST'])
@endsection
