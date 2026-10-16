@extends('layouts.admin')

@section('title', 'Edit '.$category->name)
@section('heading', 'Edit Category')

@section('content')
    @include('admin.categories.form', ['category' => $category])
@endsection
