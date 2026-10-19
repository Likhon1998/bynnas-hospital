@extends('layouts.admin')

@section('title', 'Edit department')
@section('heading', 'Edit department')

@section('content')
    @include('admin.departments._form', ['action' => route('admin.departments.update', $department), 'method' => 'PUT'])
@endsection
