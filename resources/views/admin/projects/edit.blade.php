@extends('layouts.admin')

@section('title', 'Edit '.$project->title)
@section('heading', 'Edit Case Study')

@section('content')
    @include('admin.projects.form', ['project' => $project])
@endsection
