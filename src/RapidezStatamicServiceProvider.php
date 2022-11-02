<?php

namespace Rapidez\Statamic;

use Illuminate\Support\ServiceProvider;
use TorMorten\Eventy\Facades\Eventy;
use Rapidez\Statamic\Commands\SyncProductsCommand;
use Statamic\Facades\Entry;
use Statamic\Stache\Repositories\EntryRepository as StatamicEntryRepository;
use Rapidez\Statamic\Repositories\EntryRepository;
use Statamic\Statamic;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as RenderedView;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Validator;

class RapidezStatamicServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bootCommands()
            ->bootConfig()
            ->bootRepositories()
            ->bootPublishables()
            ->bootFilters()
            ->bootComposers();
    }

    public function bootConfig() : self
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statamic.php', 'statamic');

        return $this;
    }

    public function bootFilters() : self
    {
        Eventy::addFilter('routes', fn ($routes) => array_merge($routes ?: [], [__DIR__.'/../routes/fallback.php']));

        return $this;
    }


    public function bootCommands() : self
    {
        $this->commands([
            SyncProductsCommand::class
        ]);

        return $this;
    }

    public function bootComposers() : self
    {
        if (config('statamic.get_product_collection_on_product_page')) {
            View::composer('rapidez::product.overview', function (RenderedView $view) {
                $entry = Entry::whereCollection('products')->where('sku', config('frontend.product')['sku'])->first();
                $view->with('content', $entry);
            });
        }

        View::composer('rapidez::layouts.app', function ($view) {
            foreach (GlobalSet::all() as $set) {
                foreach ($set->localizations() as $locale => $variables) {
                    if ($locale == Site::current()->handle()) {
                        $data[$set->handle()] = $variables;
                    }
                }
            }

            $view->with('globals', (object)$data);
        });

        return $this;
    }

    public function bootRepositories() : self
    {
        Statamic::repository(
            StatamicEntryRepository::class,
            EntryRepository::class
        );

        return $this;
    }


    public function bootPublishables() : self
    {
        $this->publishes([
            __DIR__.'/../resources/blueprints/collections/products/products.yaml' => resource_path('blueprints/collections/products/products.yaml'),
            __DIR__.'/../resources/content/collections/products.yaml' => base_path('content/collections/products.yaml'),
            __DIR__.'/../resources/blueprints/collections/stores/stores.yaml' => resource_path('blueprints/collections/stores/stores.yaml'),
            __DIR__.'/../resources/content/collections/stores.yaml' => base_path('content/collections/stores.yaml'),
        ], 'rapidez-collections');

        $this->publishes([
            __DIR__.'/../config/statamic.php' => config_path('statamic.php'),
        ], 'config');

        return $this;
    }
}
