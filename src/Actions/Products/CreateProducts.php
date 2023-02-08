<?php

namespace Rapidez\Statamic\Actions\Products;

use Illuminate\Support\Enumerable;
use Rapidez\Statamic\Actions\Traits\UpdateOrCreateEntry;
use Statamic\Facades\Entry;

class CreateProducts
{
    use UpdateOrCreateEntry;

    public function create(Enumerable $products, string $storeCode): Enumerable
    {
        return $products->map(fn ($product): array => [
            'sku' => $product->sku,
            'title' => $product->name,
            'visibility' => $product->visibility
        ])->each(fn (array $product) => self::updateOrCreate($product, 'products', 'sku', $storeCode));
    }
}
