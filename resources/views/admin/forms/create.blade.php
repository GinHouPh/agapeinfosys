@extends('layouts.admin')

@section('title', 'New Form')
@section('heading', 'New Form')

@section('content')
    <div class="admin-card max-w-3xl">
        @include('admin.forms.form', ['form' => new \App\Models\Form()])
    </div>
@endsection
