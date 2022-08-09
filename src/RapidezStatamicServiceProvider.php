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


class RapidezStatamicServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bootCommands()
            ->bootRepositories()
            ->bootPublishables()
            ->bootFilters()
            ->bootComposers();
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
        View::composer('rapidez::product.overview', function (RenderedView $view) {
            $entry = Entry::whereCollection('products')->where('sku', config('frontend.product')['sku'])->first();
            $view->with('content', $entry->data());
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
        ], 'rapidez-collections');

        $this->publishes([
                __DIR__.'/../resources/content/collections/products.yaml' => base_path('content/collections/products.yaml'),
        ], 'rapidez-collections');

        return $this;
    }
}
