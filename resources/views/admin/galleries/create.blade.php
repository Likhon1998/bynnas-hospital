@extends('layouts.admin')
@section('title', 'Add gallery image')
@section('heading', 'Add gallery image')
@section('content')
    @include('admin.galleries._form', ['action' => route('admin.galleries.store'), 'method' => 'POST'])
@endsection
