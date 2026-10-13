@extends('layouts.app')

@section('title', $page->title)

@section('description', $page->meta_description ?? 'Agape Infosys builds custom software, web and mobile products, and provides IT consultancy for enterprises.')

@section('content')
    {!! $html !!}
@endsection
