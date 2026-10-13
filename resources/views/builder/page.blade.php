@extends('layouts.app')

{{-- Record detail templates inherit the record's own browser title; standalone
     and flagship pages fall back to the page's meta title and description. --}}
@section('title', $pageTitle ?? $page->title)

@section('description', $pageDescription ?? ($page->meta_description ?? 'Agape Infosys builds custom software, web and mobile products, and provides IT consultancy for enterprises.'))

@section('content')
    {!! $html !!}
@endsection
