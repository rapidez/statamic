@props([
    'formHandle' => null,
    'successText' => null,
    'buttonText' => null,
    'ajaxResponse' => true,
])

@if(!isset($formHandle))
    @return
@endif

@php($form = Statamic::tag('form:create')->params(['in' => $formHandle, 'js' => 'vue'])->fetch())

@if($form['success'])
    <div>@lang('The form was submitted successfully')</div>
    @return
@endif

<form-conditions :initial-data='@json(Arr::pluck($form['fields'], 'default', 'handle'))' v-slot="{ formData }">
    <form-submission v-slot="{ submit, success }">
        <div v-cloak v-if="success">
            {!! $successText !!}
        </div>
        <form v-else @submit.prevent="submit" @attributes($form['attrs'])>
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
                    @includeFirstSafe(['form_fields.' . ($field['type'] ?? ''), 'rapidez-statamic::form_fields.' . ($field['type'] ?? '')])
                    </div>
                @endforeach
            </div>

            <x-rapidez::button.secondary type="submit">
                {!! $buttonText !!}
            </x-rapidez::button.secondary>
        </form>
    </form-submission>
</form-conditions>
