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

        Entry::query()
            ->where('collection', 'products')
            ->where('site', $storeCode)
            ->whereNotIn('sku', $productSkus)
            ->delete();
    }
}
