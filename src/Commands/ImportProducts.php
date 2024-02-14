<?php

namespace Rapidez\Statamic\Commands;

use Statamic\Facades\Site;
use Illuminate\Console\Command;
use Rapidez\Core\Facades\Rapidez;
use TorMorten\Eventy\Facades\Eventy;
use Rapidez\Statamic\Actions\StatamicEntryAction;

class ImportProducts extends Command
{
    protected $signature = 'rapidez:statamic:import:products {--site=* : Sites handles or urls to process, if none are passed it will be done for all sites (Default will be all sites)}';

    protected $description = 'Create a new product entry for a product if it does not exist.';

    public function handle()
    {
        $productModel = config('rapidez.models.product');

        /** @var StatamicEntryAction $statamicEntryAction */
        $statamicEntryAction = app(StatamicEntryAction::class);
        
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
                $statamicEntryAction::createEntry(
                    [
                        'collection' => config('rapidez.statamic.import.products.collection', 'products'),
                        'blueprint'  => config('rapidez.statamic.import.products.blueprint', 'product'),
                        'site'       => $site->handle(),
                        'linked_product' => $product->sku,
                    ],
                    array_merge([
                        'locale'       => $site->handle(),
                        'site'       => $site->handle(),
                    ], ...[Eventy::filter('rapidez.statamic.product.entry.data', $product)])
                );
            }

            $bar->advance();
        }
        $bar->finish();

        return static::SUCCESS;
    }
}
