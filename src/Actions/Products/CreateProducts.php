<?php

namespace Rapidez\Statamic\Actions\Products;

use Illuminate\Database\Eloquent\Collection;
use Statamic\Facades\Entry;

class CreateProducts
{
    public function create(Collection $products, string $storeCode): \Illuminate\Support\Collection
    {
        if ($products->isEmpty()) {
            return new Collection();
        }

        return $products->map(fn ($product) => [
            'sku' => $product->sku,
            'title' => $product->name
        ])->each(fn ($product) => Entry::updateOrCreate($product, 'products', 'sku', $storeCode));
    }
}
