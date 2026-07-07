@foreach($field['options'] as $key => $label)
    <x-rapidez::input.checkbox
        :name="$field['handle']"
        :value="$key"
        :type="$field['type']"
        :class="$field['error'] ? 'border-red-500' : ''"
        :required="in_array('required', $field['validate'] ?? [])"
        v-model="formData.{{ $field['handle'] }}"
    >
        {{ $label }}
    </x-rapidez::input.checkbox>
@endforeach