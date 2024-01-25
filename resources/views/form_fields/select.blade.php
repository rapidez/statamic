<x-rapidez::select
    :name="$field['handle']"
    :label="$field['display']"
    :value="$field['old'] ?: $field['default'] ?? ''"
    :class="$field['error'] ? 'border-red-500' : ''"
    :required="in_array('required', $field['validate'] ?? [])"
    v-model="formData.{{ $field['handle'] }}"
>
    @if (!($field['default'] ?? ''))
        <option value="null" disabled selected>
            {{ $field['placeholder'] ?? '' }}
        </option>
    @endif
    @foreach ($field['options'] ?? [] as $option => $label)
        <option value="{{ $option }}" @selected($option == $field['default'] ?? '')>
            {{ $label }}
        </option>
    @endforeach
</x-rapidez::select>
