@extends('layouts.admin')
@section('title', 'Add package')
@section('heading', 'Add package')
@section('content')
    @include('admin.health-packages._form', ['action' => route('admin.health-packages.store'), 'method' => 'POST'])
@endsection
