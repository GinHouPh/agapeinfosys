@extends('layouts.admin')

@section('title', 'Edit '.$product->name)
@section('heading', 'Edit Product')

@section('content')
    @include('admin.products.form', ['product' => $product])
@endsection
