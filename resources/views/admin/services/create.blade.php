@extends('layouts.admin')
@section('title', 'Add service')
@section('heading', 'Add service')
@section('content')
    @include('admin.services._form', ['action' => route('admin.services.store'), 'method' => 'POST'])
@endsection
