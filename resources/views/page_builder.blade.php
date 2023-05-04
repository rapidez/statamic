@foreach ($content as $set)
    @if(!View::exists('page_builder.' . $set['type']) && !View::exists('rapidez-statamic::page_builder.' . $set['type']))
        @if(!app()->environment('production'))
            <hr>View for set {{ $set['type'] }} not found.<hr>
        @endif
    @else
        @includeFirst(['page_builder.' . $set['type'], 'rapidez-statamic::page_builder.' . $set['type']], $set)
    @endif
@endforeach
