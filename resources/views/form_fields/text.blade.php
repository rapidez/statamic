<label>
    <x-rapidez::label>{{ $field['display'] }}</x-rapidez::label>
    <x-rapidez::input
        :name="$field['handle']"
        :type="$field['input_type']"
        :value="$field['old'] ?: $field['value']"
        :placeholder="$field['placeholder'] ?? false"
        :class="$field['error'] ? 'border-red-500' : ''"
        :maxlength="$field['character_limit'] ?? false"
        :required="in_array('required', $field['validate'] ?? [])"
        v-model="formData.{{ $field['handle'] }}"
    />
    @if($field['error'])
        <div class="text-red-500">{{ $field['error'] }}</div>
    @endif
</label>
