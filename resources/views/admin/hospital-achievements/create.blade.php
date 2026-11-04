@extends('layouts.admin')
@section('title', 'Add achievement')
@section('heading', 'Add achievement')
@section('content')
    @include('admin.hospital-achievements._form', ['action' => route('admin.hospital-achievements.store'), 'method' => 'POST'])
@endsection
