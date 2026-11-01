@extends('layouts.admin')
@section('title', 'Add lab test')
@section('heading', 'Add lab test')
@section('content')
    <x-admin.form-page
        :action="route('admin.labs.tests.store')"
        method="POST"
        :cancel="route('admin.labs.tests.index')"
        submit="Save test"
    >
        @include('admin.labs.tests._form')
    </x-admin.form-page>
@endsection
