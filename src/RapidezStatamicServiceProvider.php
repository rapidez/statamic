<?php

namespace Rapidez\Statamic;

use Statamic\Statamic;
use Statamic\Sites\Sites;
use Statamic\Facades\Site;
use Statamic\Facades\Entry;
use Statamic\Facades\Utility;
use Illuminate\Routing\Router;
use Rapidez\Core\Facades\Rapidez;
use Statamic\Events\GlobalSetSaved;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Rapidez\Statamic\Tags\Alternates;
use Statamic\Events\GlobalSetDeleted;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as RenderedView;
use Rapidez\Statamic\Commands\ImportBrands;
use Rapidez\Statamic\Forms\JsDrivers\Vue;
use Rapidez\Statamic\Commands\ImportProducts;
use Rapidez\Statamic\Commands\ImportCategories;
use Rapidez\Statamic\Extend\SitesLinkedToMagentoStores;
use Rapidez\Statamic\Http\Controllers\ImportsController;
use Rapidez\Statamic\Http\Controllers\StatamicRewriteController;
use Rapidez\Statamic\Http\ViewComposers\StatamicGlobalDataComposer;
use TorMorten\Eventy\Facades\Eventy;

class RapidezStatamicServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->extend(Sites::class, function () {
            return new SitesLinkedToMagentoStores(config('statamic.sites'));
        });

        $this->registerDirectives();
    }

    public function boot()
    {
        $this
            ->bootCommands()
            ->bootConfig()
            ->bootRoutes()
            ->bootViews()
            ->bootListeners()
            ->bootRunway()
            ->bootComposers()
            ->bootPublishables()
            ->bootUtilities()
            ->bootStack();

        Vue::register();
        Alternates::register();
    }

    public function registerDirectives(): static
    {
        Blade::directive('tags', function ($expression) {
            /**
             * Use Statamic::tag() in a more convenient way
             * Usage:
             *  - @tags(['products' => ['collection:products' => ['type' => 'chair']]])
             *  - @tags(['products' => 'collection:products'])
             *  - @tags('collection:products')
             * etc...
             */
            return "<?php
                foreach (Arr::wrap($expression) as \$__key => \$__value) {
                    if (is_array(\$__value) && in_array('tag', \$__value)) {
                        \$__tag = \$__value['tag'];
                        \$__params = \$__value['params'] ?? [];
                    } else if (is_array(\$__value) && count(\$__value) == 1) {
                        \$__tag = array_keys(\$__value)[0];
                        \$__params = array_values(\$__value)[0];
                    } else if (is_string(\$__value)) {
                        \$__tag = \$__value;
                        \$__params = [];
                    } else {
                        continue;
                    }
                    \$__varName = is_string(\$__key) ? \$__key : camel_case(str_replace(':', '_', \$__tag));
                    \${\$__varName} = Statamic::tag(\$__tag)->params(\$__params)->fetch();
                }
                unset(\$__tag, \$__params, \$__varName, \$__key, \$__value);
            ?>";
        });

        return $this;
    }

    public function bootCommands(): static
    {
        $this->commands([
            ImportCategories::class,
            ImportProducts::class,
            ImportBrands::class,
        ]);

        return $this;
    }

    public function bootConfig(): static
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rapidez/statamic.php', 'rapidez.statamic');

        return $this;
    }

    public function bootRoutes(): static
    {
        if (config('rapidez.statamic.routes') && $this->currentSiteIsEnabled()) {
            Rapidez::addFallbackRoute(StatamicRewriteController::class);
        }

        return $this;
    }

    public function bootViews(): static
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'rapidez-statamic');
        $this->loadViewsFrom(__DIR__.'/../resources/views/vendor/responsive-images', 'responsive-images');

        return $this;
    }

    public function bootListeners(): static
    {
        if ($this->currentSiteIsEnabled()) {
            Event::listen([GlobalSetSaved::class, GlobalSetDeleted::class], function () {
                Cache::forget('statamic-globals-' . Site::current()->handle());
            });

            Eventy::addFilter('rapidez.statamic.category.entry.data', fn($category) => [
                'title' => $category->name,
                'slug' => trim($category->url_key),
            ]);

            Eventy::addFilter('rapidez.statamic.product.entry.data', fn($product) => [
                'title' => $product->name,
                'slug' => trim($product->url_key),
            ]);

            Eventy::addFilter('rapidez.statamic.brand.entry.data', fn($brand) => [
                'title' => $brand->value_store,
                'slug' => trim($brand->value_admin),
            ]);
        }

        return $this;
    }

    public function bootRunway(): static
    {
        if (config('rapidez.statamic.runway.configure') && $this->currentSiteIsEnabled()) {
            config(['runway.resources' => array_merge(
                config('rapidez.statamic.runway.resources'),
                config('runway.resources')
            )]);
        }

        return $this;
    }

    public function bootComposers(): static
    {
        if (config('rapidez.statamic.fetch.product') && $this->currentSiteIsEnabled()) {
            View::composer('rapidez::product.overview', function (RenderedView $view) {
                $entry = Entry::query()
                    ->where('collection', 'products')
                    ->where('site', config('rapidez.store_code'))
                    ->where('linked_product', config('frontend.product.sku'))
                    ->first();

                $view->with('content', optionalDeep($entry));
            });
        }

        if (config('rapidez.statamic.fetch.category') && $this->currentSiteIsEnabled()) {
            View::composer('rapidez::category.overview', function (RenderedView $view) {
                $entry = Entry::query()
                    ->where('collection', 'categories')
                    ->where('site', config('rapidez.store_code'))
                    ->where('linked_category', config('frontend.category.entity_id'))
                    ->first();

                $view->with('content', optionalDeep($entry));
            });
        }

        if ($this->currentSiteIsEnabled()) {
            $this->app->singleton(StatamicGlobalDataComposer::class);
            View::composer('*', StatamicGlobalDataComposer::class);
        }

        return $this;
    }

    public function bootPublishables(): static
    {
        $this->publishes([
            __DIR__.'/../resources/blueprints/collections' => resource_path('blueprints/collections'),
            __DIR__.'/../resources/content/collections' => base_path('content/collections'),
            __DIR__.'/../resources/content/assets' => base_path('content/assets'),
            __DIR__.'/../resources/fieldsets' => resource_path('fieldsets'),
        ], 'rapidez-statamic-content');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/rapidez-statamic'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../config/rapidez/statamic.php' => config_path('rapidez/statamic.php'),
        ], 'config');

        return $this;
    }

    public function bootUtilities(): static
    {
        Utility::extend(function () : void {
            Utility::register('imports')
                ->icon('synchronize')
                ->action(ImportsController::class)
                ->title(__('Import'))
                ->navTitle(__('Import'))
                ->description(__('Import products or categories from Magento'))
                ->routes(function (Router $router) : void {
                    $router->post('/import-categories', [ImportsController::class, 'importCategories'])
                        ->name('import-categories');

                    $router->post('/import-products', [ImportsController::class, 'importProducts'])
                        ->name('import-products');

                    $router->post('/import-brands', [ImportsController::class, 'importBrands'])
                        ->name('import-brands');
                });
        });

        return $this;
    }

    public function bootStack(): static
    {
        View::startPush('head', view('rapidez-statamic::stack.head'));

        return $this;
    }

    public function currentSiteIsEnabled(): bool
    {
        return !config('statamic.sites.sites.' . Site::current()->handle() . '.attributes.disabled', false);
    }
}
