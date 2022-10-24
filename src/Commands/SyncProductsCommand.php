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
        $productModel = config('rapidez.models.product');
        $storeModel = config('rapidez.models.store');
        $stores = $this->argument('store') ? $storeModel::where('store_id', $this->argument('store'))->get() : $storeModel::all();

        foreach($stores as $store) {
            config()->set('rapidez.store', $store->store_id);

            $products = $productModel::selectAttributes(['sku', 'name', 'url_key'])->where('catalog_product_flat_'.config('rapidez.store').'.visibility', 4)->get();
            $store = Entry::whereCollection('stores')->where('store_id', $store->store_id)->first();

            $products->map(fn ($product) => [
                'sku' => $product->sku,
                'title' => $product->name,
                'store' => $store->id()
            ])->each(fn ($product) => Entry::updateOrCreate($product, 'products', 'sku'));

            $magentoSkus = $products->map(fn ($product) => $product->sku);
            $statamicSkus = Entry::whereCollection('products')->map(fn ($entry) => $entry->sku);

            $entries = Entry::query()->whereIn('sku', $statamicSkus->diff($magentoSkus)->toArray())->get();
            $entries->each(fn ($entry) => $entry->delete());
        }
    }
}
