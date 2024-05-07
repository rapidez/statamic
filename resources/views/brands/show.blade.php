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
    <div class="container mt-4 mb-8 lg:mb-20">
        @includeWhen(!$is_homepage, 'rapidez-statamic::breadcrumbs')

        @if ($title)
            <h1 class="font-bold text-3xl mb-5">{{ $title }}</h1>
        @endif
        @if ($brand_content)
            <div class="prose prose-green max-w-none">
                @include('rapidez-statamic::page_builder.content', ['content' => $brand_content])
            </div>
        @endif
        <x-rapidez::listing query="{ terms: { '{{ $brandAttribute->code }}.keyword': ['{{ $title }}'] } }"/>

        @include('rapidez-statamic::page_builder')
    </div>
@endsection
