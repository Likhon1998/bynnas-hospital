@extends('layouts.admin')
@section('title', 'Add leader')
@section('heading', 'Add leader')
@section('content')
    @include('admin.board-members._form', ['action' => route('admin.board-members.store'), 'method' => 'POST'])
@endsection
