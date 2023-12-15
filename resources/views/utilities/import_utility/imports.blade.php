@extends('statamic::layout')
@section('title', __('Import'))
 
@section('content')
    <div class="flex items-center justify-between">
        <h1>{{ __('Import categories') }}</h1>
    </div>
 
    <div class="mt-3 card">
        <form action="{{ cp_route('utilities.imports.import-categories') }}" method="POST">
            @csrf

            <p class="mb-2">@lang('The import of non-existing categories may be started through the button below.')</p>

            <div class="flex items-center space-x-3">
                <button type="submit" class="btn-primary">@lang("Import categories")</button>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mt-5">
        <h1>{{ __('Import products') }}</h1>
    </div>
 
    <div class="mt-3 card">
        <form action="{{ cp_route('utilities.imports.import-products') }}" method="POST">
            @csrf

            <p class="mb-2">@lang('The import of non-existing products may be started through the button below.')</p>
            <div class="flex items-center space-x-3">
                <button type="submit" class="btn-primary">@lang("Import products")</button>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mt-5">
        <h1>{{ __('Import brands') }}</h1>
    </div>
 
    <div class="mt-3 card">
        <form action="{{ cp_route('utilities.imports.import-brands') }}" method="POST">
            @csrf

            <p class="mb-2">@lang('The import of non-existing brands may be started through the button below.')</p>
            <div class="flex items-center space-x-3">
                <button type="submit" class="btn-primary">@lang("Import brands")</button>
            </div>
        </form>
    </div>
@stop