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
        public DeleteProducts $deleteProducts
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

            $products = $productModel::selectAttributes(['sku', 'name', 'url_key'])->get();
            $storeModel = Entry::whereCollection('stores')->where('store_id', $storeModel->store_id)->first();

            $products = $this->createProducts->create($products, $storeModel->id());
            $this->deleteProducts->deleteOldProducts($products, $storeModel->id);
        }

        ProductsImportedEvent::dispatch();
    }
}
