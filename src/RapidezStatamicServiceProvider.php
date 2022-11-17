<?php

namespace Rapidez\Statamic;

use Illuminate\Support\ServiceProvider;
use TorMorten\Eventy\Facades\Eventy;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Rapidez\Statamic\Commands\SyncProductsCommand;
use Rapidez\Statamic\Commands\SyncCategoriesCommand;
use Statamic\Facades\Entry;
use Statamic\Stache\Repositories\EntryRepository as StatamicEntryRepository;
use Rapidez\Statamic\Repositories\EntryRepository;
use Rapidez\Statamic\Http\StatamicDataComposer;
use Statamic\Statamic;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as RenderedView;
use Statamic\Facades\Site;
use Validator;
use Statamic\Events\GlobalSetSaved;
use Statamic\Events\GlobalSetDeleted;

class RapidezStatamicServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton(StatamicDataComposer::class);

        $this->bootCommands()
            ->bootConfig()
            ->bootRepositories()
            ->bootPublishables()
            ->bootFilters()
            ->bootComposers()
            ->bootListeners();
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
            SyncProductsCommand::class,
            SyncCategoriesCommand::class
        ]);

        return $this;
    }

    public function bootComposers() : self
    {
        if (config('statamic.get_product_collection_on_product_page')) {
            View::composer('rapidez::product.overview', function (RenderedView $view) {
                $entry = Entry::whereCollection('products')
                    ->where('locale', config('rapidez.store_code'))
                    ->where('sku', config('frontend.product.sku'))
                    ->first();
                
                $view->with('content', $entry);
            });
        }

        View::composer('*', StatamicDataComposer::class);

        return $this;
    }

    public function bootListeners() : self
    {
        Event::listen([GlobalSetSaved::class, GlobalSetDeleted::class], function() {
            Cache::forget('statamic-globals-'.Site::current()->handle());
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
            __DIR__.'/../resources/blueprints/collections/categories/categories.yaml' => resource_path('blueprints/collections/categories/categories.yaml'),
            __DIR__.'/../resources/content/collections/categories.yaml' => base_path('content/collections/categories.yaml'),
        ], 'rapidez-collections');

        $this->publishes([
            __DIR__.'/../config/statamic.php' => config_path('statamic.php'),
        ], 'config');

        return $this;
    }
}
