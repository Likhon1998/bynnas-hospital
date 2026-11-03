@extends('layouts.admin')
@section('title', 'Add board member')
@section('heading', 'Add board member')
@section('content')
    @include('admin.board-members._form', ['action' => route('admin.board-members.store'), 'method' => 'POST'])
@endsection
