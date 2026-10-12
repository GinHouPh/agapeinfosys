@extends('layouts.admin')

@section('title', 'Edit '.$service->title)
@section('heading', 'Edit Service')

@section('content')
    @include('admin.services.form', ['service' => $service])
@endsection
