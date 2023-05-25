<x-rapidez::textarea
    :name="$field['handle']"
    :label="$field['display']"
    :value="$field['value']"
    :placeholder="$field['placeholder'] ?? false"
    :class="$field['error'] ? 'border-red-500' : ''"
    :maxlength="$field['character_limit'] ?? false"
    :required="in_array('required', $field['validate'])"
/>
@if($field['error'])
    <div class="text-red-500">{{ $field['error'] }}</div>
@endif
