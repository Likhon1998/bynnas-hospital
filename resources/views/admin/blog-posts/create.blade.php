@extends('layouts.admin')
@section('title', 'Write article')
@section('heading', 'Write article')
@section('content')
    @include('admin.blog-posts._form', ['action' => route('admin.blog-posts.store'), 'method' => 'POST'])
@endsection
