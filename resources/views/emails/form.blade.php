<x-mail::message>
# {{ $form->value()->title() }}

<x-mail::table>
| | |
| --- | --- |
@foreach($fields as $field)
| **{{ $field['display'] }}** | {{ $field['value'] }} |
@endforeach
</x-mail::table>
</x-mail::message>
