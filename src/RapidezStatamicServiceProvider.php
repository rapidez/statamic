<?php

namespace Rapidez\Statamic;

use Illuminate\Support\ServiceProvider;
use TorMorten\Eventy\Facades\Eventy;
use Statamic\Facades\GlobalSet;
use Illuminate\Support\Facades\View;
use Statamic\Facades\Site;

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
        View::composer('rapidez::layouts.app', function ($view) {
            foreach (GlobalSet::all() as $set) {
                foreach ($set->localizations() as $locale => $variables) {
                    if ($locale == Site::current()->handle()) {
                        $data[$set->handle()] = $variables;
                    }
                }
            }

            $view->with('globals', (object)$data);
        });

        return $this;
    }
}
