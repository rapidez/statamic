<?php

namespace Rapidez\Statamic;

use Illuminate\Support\ServiceProvider;
use TorMorten\Eventy\Facades\Eventy;
use Statamic\Facades\GlobalSet;
use Illuminate\Support\Facades\View;

class RapidezStatamicServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bootRoutes()
            ->bootGlobals();
    }

    public function bootRoutes() : self
    {
        Eventy::addFilter('routes', fn ($routes) => array_merge($routes ?: [], [__DIR__.'/../routes/fallback.php']));

        return $this;
    }

    public function bootGlobals() : self
    {
        View::composer('*', function ($view) {
            GlobalSet::all()->each(fn ($set) => $view->with($set->handle(), $set->inCurrentSite()));
        });

        return $this;
    }
}
