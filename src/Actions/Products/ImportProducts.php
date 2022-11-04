<?php

namespace Rapidez\Statamic\Actions\Products;

use Rapidez\Statamic\Events\ProductsImportedEvent;
use Statamic\Facades\Site;

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

        foreach(Site::all() as $site) {
            $siteAttributes = $site->attributes();

            if (!isset($siteAttributes['magento_store_id'])) {
                continue;
            }

            config()->set('rapidez.store', $siteAttributes['magento_store_id']);

            $productSkus = collect();
            $productQuery = $productModel::selectAttributes(['sku', 'name', 'url_key']);

            $productQuery->chunk($this->chunkSize, function ($products) use ($site, &$productSkus) {
                $products = $this->createProducts->create($products, $site->handle());
                $productSkus = $productSkus->merge($products->map(fn ($product) => $product['sku']));
            });

            $this->deleteProducts->deleteOldProducts($productSkus, $site->handle());
        }

        ProductsImportedEvent::dispatch();
    }
}
