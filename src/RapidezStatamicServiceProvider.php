<?php

namespace Rapidez\Statamic;

use Illuminate\Support\ServiceProvider;
use TorMorten\Eventy\Facades\Eventy;
use Rapidez\Statamic\Fieldtypes\RapidezProducts;

class RapidezStatamicServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bootEventyFilters()
            ->bootStatamicFieldTypes()
            ->bootConfig();
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
        }

        return $this;
    }

    public function bootEventyFilters() : self
    {
        Eventy::addFilter('routes', fn ($routes) => array_merge($routes ?: [], [__DIR__.'/../routes/fallback.php']));

        return $this;
    }
}
