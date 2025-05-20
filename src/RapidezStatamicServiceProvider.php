<?php

namespace Rapidez\Statamic;

use Illuminate\Foundation\Bootstrap\BootProviders;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as RenderedView;
use Rapidez\Statamic\Actions\GenerateSitemapsAction;
use Rapidez\Core\Facades\Rapidez;
use Rapidez\Statamic\Commands\ImportBrands;
use Rapidez\Statamic\Commands\InstallCommand;
use Rapidez\Statamic\Commands\InvalidateCacheCommand;
use Rapidez\Statamic\Extend\SitesLinkedToMagentoStores;
use Rapidez\Statamic\Forms\JsDrivers\Vue;
use Rapidez\Statamic\Http\ViewComposers\StatamicGlobalDataComposer;
use Rapidez\Statamic\Listeners\ClearNavTreeCache;
use Rapidez\Statamic\Listeners\SetCollectionsForNav;
use Rapidez\Statamic\Tags\Alternates;
use Statamic\Events\GlobalSetDeleted;
use Statamic\Events\GlobalSetSaved;
use Statamic\Events\NavCreated;
use Statamic\Events\NavTreeSaved;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Utility;
use Statamic\Http\Controllers\FrontendController;
use Statamic\Sites\Sites;
use Statamic\StaticCaching\Middleware\Cache as StaticCache;
use Statamic\Http\Middleware\RedirectAbsoluteDomains;
use TorMorten\Eventy\Facades\Eventy;
use Statamic\Facades\Site as SiteFacade;
use Statamic\View\Cascade as StatamicCascade;
use Rapidez\Statamic\StaticCaching\CustomInvalidator;

class RapidezStatamicServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->extend(Sites::class, fn () => new SitesLinkedToMagentoStores());

        $this->app->singleton(RapidezStatamic::class);

        // Since we have our own way of exposing the globals to the view,
        // we can overwrite Statamic's functionality so we don't query for the
        // globals multiple times. In our way we cache the globals to increase performance.
        $this->app->extend(StatamicCascade::class, function () {
            return new \Rapidez\Statamic\View\Cascade(app()->request, SiteFacade::current());
        });

        $this->app->booted(function () {
            $router = app(Router::class);
            $router->pushMiddlewareToGroup('web', StaticCache::class);
            $router->pushMiddlewareToGroup('web', RedirectAbsoluteDomains::class);
        });
        $this->app->afterBootstrapping(BootProviders::class, function () {
            // Prevent infinite locks by removing the static cache from the statamic.web middleware.
            $router = app(Router::class);
            $router->removeMiddlewareFromGroup('statamic.web', StaticCache::class);
        });
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
            ->bootBuilder()
            ->bootSitemaps()
            ->bootStaticCaching()
            ->bootStack();

        Vue::register();
        Alternates::register();
    }
    
    protected function bootBuilder(): self
    {
        config(['statamic.builder' => [
            ...(config('statamic.builder') ?? []),
            ...config('rapidez.statamic.builder'),
        ]]);
            
        return $this;
    }

    public function bootCommands() : self
    {
        $this->commands([
            ImportBrands::class,
            InstallCommand::class,
            InvalidateCacheCommand::class
        ]);

        return $this;
    }

    public function bootConfig() : self
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rapidez/statamic.php', 'rapidez.statamic');
        $this->mergeConfigFrom(__DIR__ . '/../config/rapidez/statamic/builder.php', 'rapidez.statamic.builder');

        return $this;
    }

    public function bootRoutes() : self
    {
        if (config('rapidez.statamic.routes') && $this->currentSiteIsEnabled()) {
            Rapidez::addFallbackRoute([FrontendController::class, 'index']);
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
            __DIR__.'/../resources/content/assets' => base_path('content/assets'),
            __DIR__.'/../resources/fieldsets' => resource_path('fieldsets'),
            __DIR__.'/../resources/blueprints/runway' => resource_path('blueprints/vendor/runway'),
        ], 'rapidez-statamic-content');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/rapidez-statamic'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../config/rapidez/statamic.php' => config_path('rapidez/statamic.php'),
            __DIR__ . '/../config/rapidez/statamic/builder.php' => config_path('rapidez/statamic/builder.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../src/Models/User.php' => app_path('Models/User.php'),
        ], 'rapidez-user-model');

        return $this;
    }

    public function bootSitemaps(): static
    {
        Eventy::addAction('rapidez.sitemap.generate', fn() => GenerateSitemapsAction::generate(), 20, 1);

        return $this;
    }

    public function bootStaticCaching(): static
    {
        if (!config('statamic.static_caching.invalidation.class')) {
            config()->set(
                'statamic.static_caching.invalidation.class',
                CustomInvalidator::class
            );
        }
        
        return $this;
    }

    public function bootStack() : static
    {
        View::composer('rapidez::layouts.app', fn($view) => $view->getFactory()->startPush('head', view('statamic-glide-directive::partials.head')));

        return $this;
    }

    public function currentSiteIsEnabled(): bool
    {
        return !config('statamic.sites.sites.' . Site::current()->handle() . '.attributes.disabled', false);
    }

    public function getSiteHandleByStoreId(): string
    {
        $site = Site::all()
            ->filter(fn($_site) => ($_site?->attributes()['magento_store_id'] ?? null) == config('rapidez.store'))
            ->first();

        return $site?->handle() ?? config('rapidez.store_code');
    }
}
