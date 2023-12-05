<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Rapidez\Core\Facades\Rapidez;
use Rapidez\Statamic\Jobs\ImportCategoriesJob;
use ReflectionClass;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Symfony\Component\Console\Helper\ProgressBar;

class ImportProducts extends Command
{
    protected $signature = 'rapidez:statamic:import:products {--site=* : Sites handles or urls to process, if none are passed it will be done for all sites (Default will be all sites)}';

    protected $description = 'Create a new product entry for a product if it does not exist.';

    public function handle()
    {
        $productModel = config('rapidez.models.product');

        $sites = $this->option('site');
        $sites = $sites
            ? array_filter(array_map(fn($handle) => (Site::get($handle) ?: Site::findByUrl($handle)) ?: $this->output->warning(__('No site found with handle or url: :handle', ['handle' => $handle])), $sites))
            : Site::all();

        $bar = $this->output->createProgressBar(count($sites));
        $bar->start();
        foreach($sites as $site) {
            $bar->display();

            $siteAttributes = $site->attributes();
            if (!isset($siteAttributes['magento_store_id'])) {
                continue;
            }

            Rapidez::setStore($siteAttributes['magento_store_id']);

            $products = $productModel::query()
                ->selectAttributes(['entity_id', 'sku', 'name', 'url_key'])
                ->lazy();
            
            foreach ($products as $product) {
                static::createEntry(
                    [
                        'collection' => config('rapidez-statamic.import.products.collection', 'products'),
                        'blueprint'  => config('rapidez-statamic.import.products.blueprint', 'product'),
                        'site'       => $site->handle(),
                        'linked_product' => $product->sku,
                    ],
                    array_merge([
                        'locale'       => $site->handle(),
                        'site'       => $site->handle(),
                    ], ...Event::dispatch('rapidez-statamic:product-entry-data', ['product' => $product]))
                );
            }

            $bar->advance();
        }
        $bar->finish();


        return static::SUCCESS;
    }

    protected static function createEntry(array $attributes, array $values = [])
    {
        if (Entry::query()->where($attributes)->count()) {
            // Entry was already created.
            return;
        }

        /** @var \Statamic\Entries\Entry $entry */
        $entry = Entry::make();
        $values = array_merge($attributes, $values);
        
        static::setEntryData($entry, $values)->save();
    }

    protected static function setEntryData(\Statamic\Entries\Entry $entry, array $values = []) : \Statamic\Entries\Entry
    {
        $reflectedEntry = new ReflectionClass($entry);
        foreach ($values as $key => $value) {
            // Check if the key is a statamic setter
            if (!$reflectedEntry->hasMethod($key) || $reflectedEntry->getMethod($key)->getNumberOfParameters() < 1) {
                continue;
            }

            $entry->$key($value);
            unset($values[$key]);
        }

        $entry->merge($values);

        return $entry;
    }
}
