@extends('layouts.admin')
@section('title', 'Add banner')
@section('heading', 'Add banner')
@section('content')
    @include('admin.banners._form', ['action' => route('admin.banners.store'), 'method' => 'POST'])
@endsection
