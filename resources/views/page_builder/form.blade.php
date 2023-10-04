@php($form = Statamic::tag('form:create')->param('in', $form->value()->handle(), 'js' => 'vue')->fetch())

@if($form['success'])
    <div>@lang('The form was submitted successfully')</div>
    @return
@endif

<form-conditions :initial-data='@json(Arr::pluck($form['fields'], 'default', 'handle'))' v-slot="{ formData }">
    <form @attributes($form['attrs'])>
        @foreach($form['params'] as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach

        @if($form['honeypot'] ?? false)
            <input name="{!! $form['honeypot'] !!}" class="hidden" value="">
        @endif

        <div class="grid grid-cols-12 gap-3">
            @foreach($form['fields'] as $field)
                <div
                    @class([
                        'col-span-3' => $field['width'] == '25',
                        'col-span-4' => $field['width'] == '33',
                        'col-span-6' => $field['width'] == '50',
                        'col-span-8' => $field['width'] == '66',
                        'col-span-9' => $field['width'] == '75',
                        'col-span-12' => $field['width'] == '100', 
                    ])
                    v-if="{!! $form['show_field'][$field['handle']] ?? true !!}"
                >
                    @includeFirstSafe(['form_fields.' . $field['type'], 'rapidez-statamic::form_fields.' . $field['type']], $set)
                </div>
            @endforeach
        </div>

        <x-rapidez::button type="submit">
            @lang('Submit')
        </x-rapidez::button>
    </form>
</form-conditions>
