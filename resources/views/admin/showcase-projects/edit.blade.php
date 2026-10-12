@extends('layouts.admin')

@section('title', 'Edit '.$project->title)
@section('heading', 'Edit Project')

@section('content')
    @include('admin.showcase-projects.form', ['project' => $project])
@endsection
