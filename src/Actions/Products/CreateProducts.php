<?php

namespace Rapidez\Statamic\Actions\Products;

use Illuminate\Support\Enumerable;
use Statamic\Facades\Entry;

class CreateProducts
{
    public function create(Enumerable $products, string $storeCode): Enumerable
    {
        return $products->map(fn ($product): array => [
            'sku' => $product->sku,
            'title' => $product->name,
        ])->each(fn (array $product) => Entry::updateOrCreate($product, 'products', 'sku', $storeCode));
    }
}
