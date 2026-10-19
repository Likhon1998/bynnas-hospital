@extends('layouts.admin')
@section('title', 'Edit package')
@section('heading', 'Edit package')
@section('content')
    @include('admin.health-packages._form', ['action' => route('admin.health-packages.update', $package), 'method' => 'PUT'])
@endsection
