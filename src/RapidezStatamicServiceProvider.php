<?php

namespace Rapidez\Statamic;

use Illuminate\Support\ServiceProvider;
use TorMorten\Eventy\Facades\Eventy;

class RapidezStatamicServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Eventy::addFilter('routes', fn ($routes) => array_merge($routes ?: [], [__DIR__.'/../routes/fallback.php']));
    }
}
