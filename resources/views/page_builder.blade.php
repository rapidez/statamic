@foreach ($content as $set)
    @includeFirstSafe(['page_builder.' . $set['type'], 'rapidez-statamic::page_builder.' . $set['type']], $set)
@endforeach
