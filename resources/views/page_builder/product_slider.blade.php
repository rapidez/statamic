@if($products && count($products->raw()))
    <x-rapidez::productlist
        :title="$title->value() ?: false"
        :value="$products->raw()"
    />
@endif
