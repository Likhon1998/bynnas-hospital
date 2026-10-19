@extends('layouts.admin')

@section('title', 'Add department')
@section('heading', 'Add department')

@section('content')
    @include('admin.departments._form', ['action' => route('admin.departments.store'), 'method' => 'POST'])
@endsection
