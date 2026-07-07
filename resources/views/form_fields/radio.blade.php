@foreach($field['options'] as $key => $label)
    <x-rapidez::input.radio
        :name="$field['handle']"
        :id="$field['id']"
        :value="$key"
        :class="$field['error'] ? 'border-red-500' : ''"
        :type="$field['type']"
        label="{{ $label }}"
        :required="isset($field['validate']) && in_array('required', $field['validate']) ? 'required' : false"
        v-model="formData.{{ $field['handle'] }}"
    >
        {{ $label }}
    </x-rapidez::input.radio>
@endforeach