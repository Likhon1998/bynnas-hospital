@extends('layouts.admin')
@section('title', 'Edit testimonial')
@section('heading', 'Edit testimonial')
@section('content')
    @include('admin.testimonials._form', ['action' => route('admin.testimonials.update', $testimonial), 'method' => 'PUT'])
@endsection
