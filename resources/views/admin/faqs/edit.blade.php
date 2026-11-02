@extends('layouts.admin')
@section('title', 'Edit FAQ')
@section('heading', 'Edit FAQ')
@section('content')
    @include('admin.faqs._form', ['action' => route('admin.faqs.update', $faq), 'method' => 'PUT'])
@endsection
