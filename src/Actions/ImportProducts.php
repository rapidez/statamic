<?php

namespace Rapidez\Statamic\Actions;

use Statamic\Facades\Entry;
use Rapidez\Statamic\Actions\CreateProducts;
use Rapidez\Statamic\Actions\DeleteProducts;
use Rapidez\Statamic\Events\ProductsImportedEvent;

class ImportProducts
{
    public function __construct(
        public CreateProducts $createProducts,
        public DeleteProducts $deleteProducts,
        protected int $chunkSize = 200
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
            
            $productSkus = collect();
            $productQuery = $productModel::selectAttributes(['sku', 'name', 'url_key']);
            $storeModel = Entry::whereCollection('stores')->where('store_id', $storeModel->store_id)->first();

            $productQuery->chunk($this->chunkSize, function ($products) use ($storeModel, &$productSkus) {
                $products = $this->createProducts->create($products, $storeModel->id());
                $productSkus = $productSkus->merge($products->map(fn ($product) => $product['sku']));
            });

            $this->deleteProducts->deleteOldProducts($productSkus, $storeModel->id);
        }

        ProductsImportedEvent::dispatch();
    }
}
