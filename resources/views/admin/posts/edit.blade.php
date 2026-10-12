@extends('layouts.admin')

@section('title', 'Edit '.$post->title)
@section('heading', 'Edit Article')

@section('content')
    @include('admin.posts.form', ['post' => $post])
@endsection
