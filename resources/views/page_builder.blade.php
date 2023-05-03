@foreach ($content as $set)
    @includeIf('rapidez-statamic::page_builder.' . $set['type'], $set)
    @if (!View::exists('rapidez-statamic::page_builder.' . $set['type']) && !app()->environment('production'))
        <hr>View for set {{ $set['type'] }} not found.<hr>
    @endif
@endforeach
