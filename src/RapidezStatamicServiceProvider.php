<?php

namespace Rapidez\Statamic;

use Illuminate\Support\ServiceProvider;
use Rapidez\Statamic\Http\Controllers\StatamicRewriteController;
use Rapidez\Statamic\Listeners\EntrySavedListener;
use Statamic\Events\EntrySaved;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Rapidez\Statamic\Commands\DeleteProductsCommand;
use Rapidez\Statamic\Commands\SyncProductsCommand;
use Rapidez\Statamic\Commands\SyncCategoriesCommand;
use Statamic\Facades\Entry;
use Rapidez\Statamic\Http\ViewComposers\StatamicDataComposer;
use Statamic\Statamic;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as RenderedView;
use Statamic\Facades\Site;
use Rapidez\Core\Facades\Rapidez;
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
            ->bootPublishables()
            ->bootComposers()
            ->bootListeners()
            ->bootRoutes();
    }

    public function bootRoutes() : self
    {
        Rapidez::addFallbackRoute(StatamicRewriteController::class, 100);

        return $this;
    }

    public function bootConfig() : self
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statamic.php', 'statamic');

        return $this;
    }

    public function bootCommands() : self
    {
        $this->commands([
            SyncProductsCommand::class,
            DeleteProductsCommand::class,
            SyncCategoriesCommand::class
        ]);

        return $this;
    }

    public function bootComposers() : self
    {
        if (config('statamic.get_product_collection_on_product_page')) {
            View::composer('rapidez::product.overview', function (RenderedView $view) {
                $sku = config('frontend.product.sku');
                $siteHandle = config('rapidez.store_code');

                $entry = Cache::rememberForever('statamic-product-' . $sku . '-' . $siteHandle, function () use ($sku, $siteHandle) {
                    return Entry::query()
                        ->where('collection', 'products')
                        ->where('site', $siteHandle)
                        ->where('sku', $sku)
                        ->first();
                });

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

        Event::listen([EntrySaved::class], EntrySavedListener::class);

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
