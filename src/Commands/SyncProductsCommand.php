<?php

namespace Rapidez\Statamic\Commands;

use Rapidez\Core\Models\Product;
use Illuminate\Console\Command;
use Statamic\Facades\Entry;
use Statamic\Entries\EntryCollection;
use Illuminate\Support\Facades\DB;

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

            $products = $productModel::selectAttributes(['sku', 'name', 'url_key'])->where('catalog_product_flat_'.config('rapidez.store').'.visibility', 4)->get();
            $store = Entry::whereCollection('stores')->where('store_id', $store->store_id)->first();

            foreach($products->map(fn ($product) => [
                'sku' => $product->sku,
                'title' => $product->name,
                'store' => (new EntryCollection())->add($store->id())
            ]) as $product) {
                $product = Entry::updateOrCreate($product, 'products', 'sku');
            }

            $magSkus = $products->map(fn ($product) => $product->sku);
            $statSkus = Entry::whereCollection('products')->map(fn ($entry) => $entry->sku);

            foreach ($statSkus->diff($magSkus) as $sku) {
                $entries = Entry::query()->where('sku', $sku);
                foreach($entries as $entry) {
                    if ($entry) {
                        // $entry->unpublish();
                    }
                }
            }

        }

    }
}
