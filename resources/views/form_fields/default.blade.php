<div>
    @if ($field['display'] ?? false)
        <x-rapidez::label>@lang($field['display'])</x-rapidez::label>
    @endif
    {!! $field['field'] !!}
    @if($field['error'])
        <div class="text-red-500">{{ $field['error'] }}</div>
    @endif
</div>
