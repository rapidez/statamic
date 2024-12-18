<?php

namespace Rapidez\Statamic;

use Exception;
use Statamic\Sites\Sites;
use Statamic\Facades\Site;
use Statamic\Facades\Entry;
use Statamic\Facades\Utility;
use Illuminate\Routing\Router;
use Rapidez\Core\Facades\Rapidez;
use Statamic\Events\GlobalSetSaved;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Rapidez\Statamic\Tags\Alternates;
use Statamic\Events\GlobalSetDeleted;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as RenderedView;
use Rapidez\Statamic\Commands\InstallCommand;
use Rapidez\Statamic\Commands\ImportBrands;
use Rapidez\Statamic\Commands\ImportProducts;
use Rapidez\Statamic\Commands\ImportCategories;
use Rapidez\Statamic\Forms\JsDrivers\Vue;
use Rapidez\Statamic\Extend\SitesLinkedToMagentoStores;
use Rapidez\Statamic\Http\Controllers\ImportsController;
use Rapidez\Statamic\Http\Controllers\StatamicRewriteController;
use Rapidez\Statamic\Http\ViewComposers\StatamicGlobalDataComposer;
use Rapidez\Statamic\Listeners\ClearNavTreeCache;
use Rapidez\Statamic\Listeners\SetCollectionsForNav;
use Statamic\Events\NavCreated;
use Statamic\Events\NavTreeSaved;
use TorMorten\Eventy\Facades\Eventy;
use Rapidez\Statamic\Models\User;
use Illuminate\Support\Facades\Log;

class RapidezStatamicServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->extend(Sites::class, fn () => new SitesLinkedToMagentoStores());

        $this->app->singleton(RapidezStatamic::class);
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
            ->bootUserModel()
            ->bootUtilities()
            ->bootStack();

        Vue::register();
        Alternates::register();
    }

    public function bootCommands() : self
    {
        $this->commands([
            ImportCategories::class,
            ImportProducts::class,
            ImportBrands::class,
            InstallCommand::class,
        ]);

        return $this;
    }

    public function bootConfig() : self
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rapidez/statamic.php', 'rapidez.statamic');

        return $this;
    }

    public function bootRoutes() : self
    {
        if (config('rapidez.statamic.routes') && $this->currentSiteIsEnabled()) {
            Rapidez::addFallbackRoute(StatamicRewriteController::class);
        }

        return $this;
    }

    public function bootViews() : self
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'rapidez-statamic');

        return $this;
    }

    public function bootListeners() : self
    {
        if ($this->currentSiteIsEnabled()) {
            Event::listen([GlobalSetSaved::class, GlobalSetDeleted::class], function () {
                Cache::forget('statamic-globals-' . Site::selected()->handle());
            });

            Event::listen(NavCreated::class, SetCollectionsForNav::class);
            Event::listen(NavTreeSaved::class, ClearNavTreeCache::class);

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

    public function bootRunway() : self
    {
        if (config('rapidez.statamic.runway.configure') && $this->currentSiteIsEnabled()) {
            config(['runway.resources' => array_merge(
                config('rapidez.statamic.runway.resources') ?? [],
                config('runway.resources') ?? []
            )]);
        }

        return $this;
    }

    public function bootComposers() : self
    {
        if (config('rapidez.statamic.fetch.product') && $this->currentSiteIsEnabled()) {
            View::composer('rapidez::product.overview', function (RenderedView $view) {
                $entry = Entry::query()
                    ->where('collection', 'products')
                    ->where('site', $this->getSiteHandleByStoreId())
                    ->where('linked_product', config('frontend.product.sku'))
                    ->first();

                $view->with('content', optionalDeep($entry));
            });
        }

        if (config('rapidez.statamic.fetch.category') && $this->currentSiteIsEnabled()) {
            View::composer('rapidez::category.overview', function (RenderedView $view) {
                $entry = Entry::query()
                    ->where('collection', 'categories')
                    ->where('site', $this->getSiteHandleByStoreId())
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

    public function bootPublishables() : self
    {
        $this->publishes([
            __DIR__.'/../resources/blueprints/collections' => resource_path('blueprints/collections'),
            __DIR__.'/../resources/content/collections' => base_path('content/collections'),
            __DIR__.'/../resources/content/assets' => base_path('content/assets'),
            __DIR__.'/../resources/fieldsets' => resource_path('fieldsets'),
            __DIR__.'/../resources/blueprints/runway' => resource_path('blueprints/vendor/runway'),
        ], 'rapidez-statamic-content');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/rapidez-statamic'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../config/rapidez/statamic.php' => config_path('rapidez/statamic.php'),
        ], 'config');

        return $this;
    }

    public function bootUserModel() : static
    {
        if (!class_exists('\App\Models\User')) {
            config(['auth.providers.users.model' => User::class]);
        }

        $migrationPath = database_path('migrations/0001_01_01_000000_create_users_table.php');

        if (!file_exists($migrationPath)) {
            try {
                $userTable = file_get_contents('https://raw.githubusercontent.com/laravel/laravel/refs/heads/11.x/database/migrations/0001_01_01_000000_create_users_table.php');

                if (!$userTable) {
                    throw new Exception('Failed to download the migration file.');
                }

                file_put_contents($migrationPath, $userTable);
            } catch (Exception $e) {
                Log::error('Error downloading migration file: ' . $e->getMessage());
            }
        }

        return $this;
    }

    public function bootUtilities() : static
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

    public function bootStack() : static
    {
        if (! $this->app->runningInConsole()) {
            View::startPush('head', view('statamic-glide-directive::partials.head'));
        }

        return $this;
    }

    public function currentSiteIsEnabled(): bool
    {
        return !config('statamic.sites.sites.' . Site::current()->handle() . '.attributes.disabled', false);
    }

    public function getSiteHandleByStoreId(): string
    {
        $site = Site::all()
            ->filter(fn($_site) => ($_site?->attributes()['magento_store_id'] ?? null) === config('rapidez.store'))
            ->first();

        return $site?->handle() ?? config('rapidez.store_code');
    }
}
