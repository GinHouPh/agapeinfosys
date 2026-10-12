@extends('layouts.admin')

@section('title', 'Edit '.$testimonial->name)
@section('heading', 'Edit Testimonial')

@section('content')
    @include('admin.testimonials.form', ['testimonial' => $testimonial])
@endsection
