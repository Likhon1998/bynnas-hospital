@extends('layouts.admin')
@section('title', 'Add acknowledgement')
@section('heading', 'Add acknowledgement')
@section('content')
    @include('admin.acknowledgements._form', ['action' => route('admin.acknowledgements.store'), 'method' => 'POST'])
@endsection
