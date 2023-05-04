@extends('rapidez::layouts.app')

@section('title', $meta_title->value() ?: $title)
@section('description', $meta_description)

@section('content')
    @includeWhen(!$is_homepage, 'rapidez-statamic::breadcrumbs')
    @include('rapidez-statamic::page_builder')
@endsection
