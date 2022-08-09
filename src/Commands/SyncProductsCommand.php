<?php

namespace Rapidez\Statamic\Commands;

use Rapidez\Core\Models\Product;
use Illuminate\Console\Command;
use Statamic\Facades\Entry;

class SyncProductsCommand extends Command
{
    protected $signature = 'rapidez:statamic:sync {store?}';

    protected $description = 'Sync the Magento products to Statamic.';

    public function handle()
    {
        $this->call('cache:clear');

        $productModel = config('rapidez.models.product');
        $storeModel = config('rapidez.models.store');
        $stores = $this->argument('store') ? $storeModel::where('store_id', $this->argument('store'))->get() : $storeModel::all();

        foreach($stores as $store) {
            config()->set('rapidez.store', $store->store_id);
            $products = $productModel::selectAttributes(['sku', 'name', 'url_key'])->get();

            foreach($products->map(fn ($product) => [
                'sku' => $product->sku,
                'title' => $product->name,
                'store' => config('rapidez.store')
            ]) as $product) {
                $product = Entry::updateOrCreate($product);
            }

            $magSkus = $products->map(fn ($product) => $product->sku);
            $statSkus = Entry::whereCollection('products')->map(fn ($entry) => $entry->sku);

            foreach ($statSkus->diff($magSkus) as $sku) {
                $entries = Entry::query()->where('sku', $sku);
                foreach($entries as $entry) {
                    if ($entry) {
                        $entry->unpublish();
                    }
                }
            }

        }

    }
}
