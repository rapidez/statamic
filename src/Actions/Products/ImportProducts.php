<?php

namespace Rapidez\Statamic\Actions\Products;

use Illuminate\Support\Facades\DB;
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
            $childIds = DB::table('catalog_product_super_link')->select(['product_id'])->get()->pluck('product_id');
            $flat = (new $productModel())->getTable();
            $productQuery = $productModel::selectOnlyIndexable()
                ->where($flat . '.type_id', 'configurable')
                ->orWhereNotIn($flat . '.entity_id', $childIds->toArray())
                ->withEventyGlobalScopes('statamic.product.scopes');

            $productQuery->chunk($this->chunkSize, function ($products) use ($site, &$productSkus) {
                $products = $this->createProducts->create($products, $site->handle());
                $productSkus = $productSkus->merge($products->map(fn ($product) => $product['sku']));
            });

            $this->deleteProducts->deleteOldProducts($productSkus, $site->handle());
        }

        ProductsImportedEvent::dispatch();
    }
}
