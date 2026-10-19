@extends('layouts.admin')
@section('title', 'Edit article')
@section('heading', 'Edit article')
@section('content')
    @include('admin.blog-posts._form', ['action' => route('admin.blog-posts.update', $post), 'method' => 'PUT'])
@endsection
