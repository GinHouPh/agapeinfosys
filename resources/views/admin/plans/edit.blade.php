@extends('layouts.admin')

@section('title', 'Edit '.$plan->name)
@section('heading', 'Edit Plan')

@section('content')
    @include('admin.plans.form', ['plan' => $plan])
@endsection
