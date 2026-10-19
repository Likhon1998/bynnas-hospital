@extends('layouts.admin')
@section('title', 'Edit banner')
@section('heading', 'Edit banner')
@section('content')
    @include('admin.banners._form', ['action' => route('admin.banners.update', $banner), 'method' => 'PUT'])
@endsection
