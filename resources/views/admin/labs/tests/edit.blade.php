@extends('layouts.admin')
@section('title', 'Edit '.$test->name)
@section('heading', $test->name)
@section('content')
    <x-admin.form-page
        :action="route('admin.labs.tests.update', $test)"
        method="PUT"
        :cancel="route('admin.labs.tests.index')"
        submit="Update test"
    >
        @include('admin.labs.tests._form', ['test' => $test])
    </x-admin.form-page>
@endsection
