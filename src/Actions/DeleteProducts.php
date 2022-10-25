<?php

namespace Rapidez\Statamic\Actions;

use Statamic\Facades\Entry;
use Illuminate\Support\Collection;

class DeleteProducts
{
    public function deleteOldProducts(Collection $products, ?string $storeId): void
    {
        if (!$products) {
            return;
        }

        $magentoSkus = $products->map(fn ($product) => $product['sku']);

        $deletedProducts = Entry::whereCollection('products')->filter(function ($deletedProduct) use ($magentoSkus, $storeId) {
            return !$magentoSkus->contains($deletedProduct->sku) && $deletedProduct->values()->get('store') == $storeId;
        });

        $deletedProducts->each(function ($deletedProduct) {
            $deletedProduct->delete();
        });
    }
}
