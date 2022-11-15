<?php

namespace Rapidez\Statamic\Actions\Products;

use Illuminate\Support\Collection;
use Statamic\Facades\Entry;

class DeleteProducts
{
    public function deleteOldProducts(Collection $productSkus, ?string $storeCode): void
    {
        if ($productSkus->isEmpty()) {
            return;
        }

        $deletedProducts = Entry::whereCollection('products')->where('locale', $storeCode)->filter(fn ($deletedProduct) => !$productSkus->contains($deletedProduct->sku));
        $deletedProducts->each(fn ($deletedProduct) => $deletedProduct->delete());
    }
}
