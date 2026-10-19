@extends('layouts.admin')
@section('title', 'Edit bill')
@section('heading', 'Edit bill')
@section('content')
    @include('admin.bills._form', ['action' => route('admin.bills.update', $bill), 'method' => 'PUT'])
@endsection
