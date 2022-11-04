<?php

namespace Rapidez\Statamic\Actions;

use Statamic\Facades\Entry;
use Illuminate\Database\Eloquent\Collection;

class CreateProducts
{
    public function create(Collection $products, string $storeId): \Illuminate\Support\Collection
    {
        if (!$products) {
            return new Collection();
        }

        return $products->map(fn ($product) => [
            'sku' => $product->sku,
            'title' => $product->name,
            'store' => $storeId
        ])->each(fn ($product) => Entry::updateOrCreate($product, 'products', 'sku'));
    }
}
