<?php

namespace Rapidez\Statamic\Actions;

use Statamic\Facades\Entry;
use Illuminate\Support\Collection;

class DeleteProducts
{
    public function delete(Collection $products): void
    {
        if (!$products) {
            return;
        }

        $magentoSkus = $products->map(fn ($product) => $product['sku']);
        $statamicSkus = Entry::whereCollection('products')->map(fn ($entry) => $entry->sku);

        $entries = Entry::query()->whereIn('sku', $statamicSkus->diff($magentoSkus)->toArray())->get();
        $entries->each(fn ($entry) => $entry->delete());
    }
}
