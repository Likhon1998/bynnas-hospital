@extends('layouts.admin')
@section('title', 'Add category')
@section('heading', 'Add category')
@section('content')
    @include('admin.blog-categories._form', ['action' => route('admin.blog-categories.store'), 'method' => 'POST'])
@endsection
