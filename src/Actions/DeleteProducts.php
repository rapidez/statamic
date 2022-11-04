<?php

namespace Rapidez\Statamic\Actions;

use Statamic\Facades\Entry;
use Illuminate\Support\Collection;

class DeleteProducts
{
    public function deleteOldProducts(Collection $productSkus, ?string $storeId): void
    {
        if ($productSkus->isEmpty()) {
            return;
        }

        $deletedProducts = Entry::whereCollection('products')->filter(function ($deletedProduct) use ($productSkus, $storeId) {
            return !$productSkus->contains($deletedProduct->sku) && $deletedProduct->values()->get('store') == $storeId;
        });

        $deletedProducts->each(function ($deletedProduct) {
            $deletedProduct->delete();
        });
    }
}
