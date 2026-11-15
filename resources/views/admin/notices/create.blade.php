@extends('layouts.admin')
@section('title', 'Add notice')
@section('heading', 'Add notice')
@section('content')
    @include('admin.notices._form', ['action' => route('admin.notices.store'), 'method' => 'POST'])
@endsection
