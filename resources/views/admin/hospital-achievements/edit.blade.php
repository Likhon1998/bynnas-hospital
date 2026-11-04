@extends('layouts.admin')
@section('title', 'Edit achievement')
@section('heading', 'Edit achievement')
@section('content')
    @include('admin.hospital-achievements._form', [
        'action' => route('admin.hospital-achievements.update', $achievement),
        'method' => 'PUT',
    ])
@endsection
