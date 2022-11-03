<?php

namespace Rapidez\Statamic\Actions;

use Statamic\Facades\Entry;
use Illuminate\Database\Eloquent\Collection;

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
        ])->each(function ($product) use ($storeCode) {
            Entry::updateOrCreate($product, 'products', 'sku', $storeCode);
        });
    }
}
