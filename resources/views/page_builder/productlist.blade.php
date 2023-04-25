@if($products && count($products->value()))
    <x-rapidez::productlist
        :title="$title ?: false"
        :value="array_map('trim', explode(',', $products->value()))"
    />
@endif
