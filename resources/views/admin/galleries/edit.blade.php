@extends('layouts.admin')
@section('title', 'Edit gallery image')
@section('heading', 'Edit gallery image')
@section('content')
    @include('admin.galleries._form', ['action' => route('admin.galleries.update', $item), 'method' => 'PUT'])
@endsection
