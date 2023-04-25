@foreach ($content as $set)
    @includeIf('page_builder.' . $set['type'], $set)
    @if (!View::exists('page_builder.' . $set['type']) && !app()->environment('production'))
        <hr>View for set {{ $set['type'] }} not found.<hr>
    @endif
@endforeach
