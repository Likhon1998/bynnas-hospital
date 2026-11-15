@extends('layouts.admin')
@section('title', 'Edit acknowledgement')
@section('heading', 'Edit acknowledgement')
@section('content')
    @include('admin.acknowledgements._form', ['action' => route('admin.acknowledgements.update', $acknowledgement), 'method' => 'PUT'])
@endsection
