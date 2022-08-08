<?php

namespace Rapidez\Statamic;

use Illuminate\Support\ServiceProvider;
use TorMorten\Eventy\Facades\Eventy;
use Rapidez\Statamic\Fieldtypes\RapidezProducts;
use Rapidez\Core\Http\ViewComposers\ConfigComposer;
use Illuminate\Support\Facades\View;

class RapidezStatamicServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bootEventyFilters()
            ->bootStatamicFieldTypes()
            ->bootConfig()
            ->bootViews();
    }

    protected function bootViews(): self
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'rapidez');

        $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/rapidez/statamic'),
            ], 'views');

        View::composer('statamic::layout', ConfigComposer::class);

        return $this;
    }


    public function bootConfig() : self
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rapidez/statamic.php', 'rapidez.statamic');
        $this->publishes([
                __DIR__.'/../config/rapidez/statamic.php' => config_path('rapidez/statamic.php'),
            ], 'config');

        return $this;
    }

    public function bootStatamicFieldTypes() : self
    {
        if (config('rapidez.statamic.field_types')) {
            RapidezProducts::register();

            $this->publishes([
                __DIR__.'/../resources/fieldsets/' => resource_path('fieldsets'),
            ], 'fieldsets');
        }

        return $this;
    }

    public function bootEventyFilters() : self
    {
        Eventy::addFilter('routes', fn ($routes) => array_merge($routes ?: [], [__DIR__.'/../routes/fallback.php']));

        return $this;
    }
}
