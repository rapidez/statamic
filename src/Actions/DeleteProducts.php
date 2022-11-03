<?php

namespace Rapidez\Statamic\Actions;

use Statamic\Facades\Entry;
use Illuminate\Support\Collection;

class DeleteProducts
{
    public function deleteOldProducts(Collection $productSkus, ?string $storeCode): void
    {
        if ($productSkus->isEmpty()) {
            return;
        }

        $deletedProducts = Entry::whereCollection('products')->where('locale', $storeCode)->filter(function ($deletedProduct) use ($productSkus, $storeCode) {
            return !$productSkus->contains($deletedProduct->sku);
        });

        $deletedProducts->each(function ($deletedProduct) {
            $deletedProduct->delete();
        });
    }
}
