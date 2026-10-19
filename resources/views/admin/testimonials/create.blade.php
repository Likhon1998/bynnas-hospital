@extends('layouts.admin')
@section('title', 'Add testimonial')
@section('heading', 'Add testimonial')
@section('content')
    @include('admin.testimonials._form', ['action' => route('admin.testimonials.store'), 'method' => 'POST'])
@endsection
