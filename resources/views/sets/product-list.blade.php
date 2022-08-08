<x-rapidez::productlist :value="explode(',', $products)">
    <{{ $tag ?? 'strong' }} class="font-bold text-2xl mt-5" slot="renderResultStats">
        @lang($title)
    </{{ $tag ?? 'strong' }}>

</x-rapidez::productlist>
