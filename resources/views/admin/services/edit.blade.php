@extends('layouts.admin')
@section('title', 'Edit service')
@section('heading', 'Edit service')
@section('content')
    @include('admin.services._form', ['action' => route('admin.services.update', $service), 'method' => 'PUT'])
@endsection
