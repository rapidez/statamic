@if ($field['display'] ?? false)
    <x-rapidez::label>@lang($field['display'])</x-rapidez::label>
@endif
<div class="flex flex-col gap-3">
    @foreach ($field['fields'] ?? [] as $groupField)
        @include('rapidez-statamic::form_fields.default', ['field' => $groupField])
    @endforeach
</div>
