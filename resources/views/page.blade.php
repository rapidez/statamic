@extends('rapidez::layouts.app')

@section('title', $meta_title->value() ?: $title)
@section('description', $meta_description)
@push('head')
    @foreach(Statamic::tag('alternates')->params(compact('page')) as $lang => $url)
        <link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}" />
    @endforeach
@endpush

@section('content')
    @includeWhen(!$is_homepage, 'rapidez-statamic::breadcrumbs')
    @include('rapidez-statamic::page_builder')
@endsection
