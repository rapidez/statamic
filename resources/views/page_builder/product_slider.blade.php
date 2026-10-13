@if($products && count($products->raw()))
    <x-rapidez::productlist
        :title="$title->value() ?: false"
        :value="$products->raw()"
        field="entity_id"
    />
@endif
