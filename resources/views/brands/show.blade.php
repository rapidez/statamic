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

    @if ($title)
        <h1 class="font-bold text-3xl mb-5">{{ $title }}</h1>
    @endif
    @if ($description)
        <div class="prose prose-green max-w-none">
            @content($description)
        </div>
    @endif
    <x-rapidez::listing query="{ terms: { 'manufacturer.keyword': ['{{ $title }}'] } }"/>

    @include('rapidez-statamic::page_builder')
@endsection
