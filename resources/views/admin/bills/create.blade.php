@extends('layouts.admin')
@section('title', 'Generate bill')
@section('heading', 'Generate bill')
@section('content')
    @include('admin.bills._form', ['action' => route('admin.bills.store'), 'method' => 'POST'])
@endsection
