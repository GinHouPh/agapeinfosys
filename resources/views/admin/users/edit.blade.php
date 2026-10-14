@extends('layouts.admin')

@section('title', 'Edit '.$user->name)
@section('heading', 'Edit client')

@section('content')
    @include('admin.users.form', ['user' => $user])
@endsection
