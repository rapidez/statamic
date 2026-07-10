@if ($field['display'] ?? false)
    <x-rapidez::label>@lang($field['display'])</x-rapidez::label>
@endif
@foreach($field['options'] as $key => $label)
    <x-rapidez::input.checkbox
        :name="$field['handle']"
        :id="$key"
        :value="$key"
        :type="$field['type']"
        :class="$field['error'] ? 'border-red-500' : ''"
        :required="in_array('required', $field['validate'] ?? [])"
        v-model="formData.{{ $key }}"
    >
        {{ $label }}
    </x-rapidez::input.checkbox>
@endforeach