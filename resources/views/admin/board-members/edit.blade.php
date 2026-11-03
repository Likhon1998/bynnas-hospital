@extends('layouts.admin')
@section('title', 'Edit board member')
@section('heading', 'Edit board member')
@section('content')
    @include('admin.board-members._form', ['action' => route('admin.board-members.update', $boardMember), 'method' => 'PUT'])
@endsection
