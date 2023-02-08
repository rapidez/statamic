<?php

namespace Rapidez\Statamic\Actions\Products;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\DB;
use Rapidez\Core\Models\Model;
use Rapidez\Statamic\Events\ProductsImportedEvent;
use Rapidez\Statamic\Jobs\CreateProductsJob;
use Statamic\Facades\Site;

class ImportProducts
{
    public function __construct(
        public CreateProducts $createProducts,
        public DeleteProducts $deleteProducts,
        protected int $chunkSize = 500
    ) {
    }

    public function import(?Carbon $updatedAt = null, ?string $store = null, ?bool $addHidden = false): void
    {
        $productModel = config('rapidez.models.product');

        $sites = Site::all();

        if ($store !== null) {
            $sites = collect([$store => Site::get($store)]);
        }

        $sites->each(function ($site) use ($productModel, $updatedAt, $addHidden) {
            $siteAttributes = $site->attributes();

            if (!isset($siteAttributes['magento_store_id'])) {
                return;
            }

            config()->set('rapidez.store', $siteAttributes['magento_store_id']);

            $childIds = DB::table('catalog_product_super_link')->pluck('product_id');

            /** @var Model $flat */
            $flat = new $productModel();

            $productQuery = $productModel::selectOnlyIndexable()
                ->when($updatedAt !== null, function (Builder $builder) use ($updatedAt): void {
                    $builder->where($builder->qualifyColumn('updated_at'), '>=', $updatedAt);
                })
                ->when(!$addHidden, function(Builder $builder) use ($childIds): void {
                    $builder->where(function (Builder $builder) use ($childIds): void {
                        $builder
                            ->where($builder->qualifyColumn('type_id'), 'configurable')
                            ->orWhereNotIn($builder->qualifyColumn('entity_id'), $childIds->toArray());
                    });
                })
                ->withEventyGlobalScopes('statamic.product.scopes');

            $productQuery->chunk($this->chunkSize, function (Enumerable $products) use ($site): void {
                CreateProductsJob::dispatch($products, $site->handle());
            });
        });

        ProductsImportedEvent::dispatch();
    }

    public function delete(?string $store = null): void
    {
        $productModel = config('rapidez.models.product');

        $sites = Site::all();

        if ($store !== null) {
            $sites = collect([$store => Site::get($store)]);
        }

        $sites->each(function ($site) use ($productModel) {
            $siteAttributes = $site->attributes();

            if (!isset($siteAttributes['magento_store_id'])) {
                return;
            }

            config()->set('rapidez.store', $siteAttributes['magento_store_id']);

            $childIds = DB::table('catalog_product_super_link')->pluck('product_id');

            /** @var Model $flat */
            $flat = new $productModel();

            $productSkus = collect();

            $productQuery = $productModel::selectAttributes(['sku', 'name'])
                ->withoutGlobalScopes()
                ->withEventyGlobalScopes('statamic.product.scopes');

            $productQuery->chunk($this->chunkSize, function (Enumerable $products) use ($site, &$productSkus): void {
                $productSkus = $productSkus->merge($products->pluck('sku'));
            });

            $this->deleteProducts->deleteOldProducts($productSkus, $site->handle());
        });
    }
}
