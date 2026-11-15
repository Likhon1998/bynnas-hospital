@extends('layouts.admin')
@section('title', 'Edit notice')
@section('heading', 'Edit notice')
@section('content')
    @include('admin.notices._form', ['action' => route('admin.notices.update', $notice), 'method' => 'PUT'])
@endsection
