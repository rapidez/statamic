<?php

namespace Rapidez\Statamic;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as RenderedView;
use Rapidez\Core\Facades\Rapidez;
use Rapidez\Statamic\Actions\ImportBrands as ImportBrandsAction;
use Rapidez\Statamic\Commands\ImportBrands;
use Rapidez\Statamic\Commands\InstallCommand;
use Rapidez\Statamic\Contracts\ImportsBrands;
use Rapidez\Statamic\Extend\SitesLinkedToMagentoStores;
use Rapidez\Statamic\Forms\JsDrivers\Vue;
use Rapidez\Statamic\Http\Controllers\ImportsController;
use Rapidez\Statamic\Http\Controllers\StatamicRewriteController;
use Rapidez\Statamic\Http\ViewComposers\StatamicGlobalDataComposer;
use Rapidez\Statamic\Jobs\ImportBrandsJob;
use Rapidez\Statamic\Tags\Alternates;
use Statamic\Events\GlobalSetDeleted;
use Statamic\Events\GlobalSetSaved;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Utility;
use Statamic\Sites\Sites;
use TorMorten\Eventy\Facades\Eventy;

class RapidezStatamicServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->extend(Sites::class, function () {
            return new SitesLinkedToMagentoStores(config('statamic.sites'));
        });
    }

    public function boot()
    {
        $this
            ->bootActions()
            ->bootCommands()
            ->bootConfig()
            ->bootRoutes()
            ->bootViews()
            ->bootListeners()
            ->bootRunway()
            ->bootComposers()
            ->bootPublishables()
            ->bootUtilities()
            ->bootStack()
            ->bootBuilder()
            ->bootJobs();

        Vue::register();
        Alternates::register();
    }
    
    public function bootJobs(): self
    {
        Schedule::job(new ImportBrandsJob)->dailyAt('00:00');

        return $this;
    }

    public function bootActions() : self
    {
        $this->app->bind(ImportsBrands::class, ImportBrandsAction::class);

        return $this;
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
                ->description(__('Import brands from Magento'))
                ->routes(function (Router $router) : void {
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
