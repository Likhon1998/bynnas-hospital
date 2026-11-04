@extends('layouts.admin')
@section('title', 'Edit leader')
@section('heading', 'Edit leader')
@section('content')
    @include('admin.board-members._form', ['action' => route('admin.board-members.update', $boardMember), 'method' => 'PUT'])
@endsection
