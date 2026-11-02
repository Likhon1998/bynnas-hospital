@extends('layouts.admin')
@section('title', 'Add FAQ')
@section('heading', 'Add FAQ')
@section('content')
    @include('admin.faqs._form', ['action' => route('admin.faqs.store'), 'method' => 'POST'])
@endsection
