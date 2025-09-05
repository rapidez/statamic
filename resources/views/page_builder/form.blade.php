<div class="component form">
    <div class="container">
        <x-rapidez-statamic::form :formHandle="$form->value()->handle()" :set="$set" successText="{{ __('The form was submitted successfully') }}" buttonText="{{ __('Submit') }}" />
    </div>
</div>
