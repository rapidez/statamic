@extends('rapidez::layouts.app')

@section('title', $meta_title->value() ?: $title)
@section('description', $meta_description)
@push('head')
    @foreach(Statamic::tag('alternates')->params(compact('page')) as $lang => $url)
        <link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}" />
    @endforeach
@endpush

@php($brandAttribute = \Rapidez\Core\Models\Attribute::find(config('rapidez.statamic.runway.brand_attribute_id')))

@section('content')
    <div class="container">
        @includeWhen(!$is_homepage, 'rapidez-statamic::breadcrumbs')

        @if ($title)
            <h1 class="font-bold text-3xl mb-5">{{ $title }}</h1>
        @endif
        @if ($content)
            @include('rapidez-statamic::page_builder', ['content' => $content])
        @endif
        <x-rapidez::listing filter-query-string="{{ $brandAttribute->code }}:{{ $title }}" />

        @include('rapidez-statamic::page_builder', ['content' => $bottom_content])
    </div>
@endsection
