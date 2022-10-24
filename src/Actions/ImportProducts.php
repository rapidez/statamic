<?php

namespace Rapidez\Statamic\Actions;

use Statamic\Facades\Entry;
use Rapidez\Statamic\Events\ProductsImportedEvent;

class ImportProducts
{
    public function __construct(
    ) {
    }

    public function import(
        ?string $store = null
    ): void
    {
        $productModel = config('rapidez.models.product');
        $storeModel = config('rapidez.models.store');
        $storeModels = $store ? $storeModel::where('store_id', $store)->get() : $storeModel::all();

        foreach($storeModels as $storeModel) {
            config()->set('rapidez.store', $storeModel->store_id);

            $products = $productModel::selectAttributes(['sku', 'name', 'url_key'])->where('catalog_product_flat_'.config('rapidez.store').'.visibility', 4)->get();
            $storeModel = Entry::whereCollection('stores')->where('store_id', $storeModel->store_id)->first();

            $products->map(fn ($product) => [
                'sku' => $product->sku,
                'title' => $product->name,
                'store' => $storeModel->id()
            ])->each(fn ($product) => Entry::updateOrCreate($product, 'products', 'sku'));

            $magentoSkus = $products->map(fn ($product) => $product->sku);
            $statamicSkus = Entry::whereCollection('products')->map(fn ($entry) => $entry->sku);

            $entries = Entry::query()->whereIn('sku', $statamicSkus->diff($magentoSkus)->toArray())->get();
            $entries->each(fn ($entry) => $entry->delete());
        }

        ProductsImportedEvent::dispatch();
    }
}
