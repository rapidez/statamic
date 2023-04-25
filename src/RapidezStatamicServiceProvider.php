<?php

namespace Rapidez\Statamic;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as RenderedView;
use Rapidez\Core\Facades\Rapidez;
use Rapidez\Statamic\Http\Controllers\StatamicRewriteController;
use Rapidez\Statamic\Http\ViewComposers\StatamicGlobalDataComposer;
use Statamic\Events\GlobalSetDeleted;
use Statamic\Events\GlobalSetSaved;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Statamic;

class RapidezStatamicServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this
            ->bootConfig()
            ->bootViews()
            ->bootPublishables()
            ->bootComposers()
            ->bootListeners()
            ->bootRoutes()
            ->bootRunway();
    }

    public function bootRoutes() : self
    {
        if (config('rapidez-statamic.routes')) {
            Rapidez::addFallbackRoute(StatamicRewriteController::class, 100);
        }

        return $this;
    }

    public function bootViews() : self
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'rapidez-statamic');

        return $this;
    }

    public function bootConfig() : self
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rapidez-statamic.php', 'rapidez-statamic');

        return $this;
    }

    public function bootComposers() : self
    {
        if (config('rapidez-statamic.fetch.product')) {
            View::composer('rapidez::product.overview', function (RenderedView $view) {
                $entry = Entry::query()
                    ->where('collection', 'products')
                    ->where('site', config('rapidez.store_code'))
                    ->where('linked_product', config('frontend.product.sku'))
                    ->first();

                $view->with('content', $entry);
            });
        }

        if (config('rapidez-statamic.fetch.category')) {
            View::composer('rapidez::category.overview', function (RenderedView $view) {
                $entry = Entry::query()
                    ->where('collection', 'categories')
                    ->where('site', config('rapidez.store_code'))
                    ->where('linked_category', config('frontend.category.entity_id'))
                    ->first();

                $view->with('content', $entry);
            });
        }

        $this->app->singleton(StatamicGlobalDataComposer::class);
        View::composer('*', StatamicGlobalDataComposer::class);

        return $this;
    }

    public function bootListeners() : self
    {
        Event::listen([GlobalSetSaved::class, GlobalSetDeleted::class], function() {
            Cache::forget('statamic-globals-'.Site::current()->handle());
        });

        return $this;
    }

    public function bootPublishables() : self
    {
        $this->publishes([
            __DIR__.'/../resources/blueprints/collections/products/product.yaml' => resource_path('blueprints/collections/products/product.yaml'),
            __DIR__.'/../resources/content/collections/products.yaml' => base_path('content/collections/products.yaml'),

            __DIR__.'/../resources/blueprints/collections/categories/category.yaml' => resource_path('blueprints/collections/categories/category.yaml'),
            __DIR__.'/../resources/content/collections/categories.yaml' => base_path('content/collections/categories.yaml'),

            __DIR__.'/../resources/blueprints/collections/pages/page.yaml' => resource_path('blueprints/collections/pages/page.yaml'),
            __DIR__.'/../resources/content/collections/pages.yaml' => base_path('content/collections/pages.yaml'),

            // TODO: Add page builder
        ], 'rapidez-collections');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/rapidez-statamic'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../config/rapidez-statamic.php' => config_path('rapidez-statamic.php'),
        ], 'config');

        return $this;
    }

    public function bootRunway() : self
    {
        if (config('rapidez-statamic.runway.configure')) {
            config(['runway.resources' => array_merge(
                config('rapidez-statamic.runway.resources'),
                config('runway.resources')
            )]);
        }

        return $this;
    }
}
