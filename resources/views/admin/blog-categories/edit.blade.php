@extends('layouts.admin')
@section('title', 'Edit category')
@section('heading', 'Edit category')
@section('content')
    @include('admin.blog-categories._form', ['action' => route('admin.blog-categories.update', $category), 'method' => 'PUT'])
@endsection
