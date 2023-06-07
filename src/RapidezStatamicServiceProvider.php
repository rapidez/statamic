<?php

namespace Rapidez\Statamic;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as RenderedView;
use Rapidez\Core\Facades\Rapidez;
use Rapidez\Statamic\Forms\JsDrivers\Vue;
use Rapidez\Statamic\Http\Controllers\StatamicRewriteController;
use Rapidez\Statamic\Http\ViewComposers\StatamicGlobalDataComposer;
use Statamic\Events\GlobalSetDeleted;
use Statamic\Events\GlobalSetSaved;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Statamic;

class RapidezStatamicServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this
            ->bootConfig()
            ->bootRoutes()
            ->bootViews()
            ->bootListeners()
            ->bootRunway()
            ->bootComposers()
            ->bootBladeDirectives()
            ->bootPublishables();

        Vue::register();
    }

    public function bootConfig() : self
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rapidez-statamic.php', 'rapidez-statamic');

        return $this;
    }

    public function bootRoutes() : self
    {
        if (config('rapidez-statamic.routes')) {
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
        Event::listen([GlobalSetSaved::class, GlobalSetDeleted::class], function() {
            Cache::forget('statamic-globals-'.Site::current()->handle());
        });

        return $this;
    }

    public function bootRunway() : self
    {
        if (config('rapidez-statamic.runway.configure')) {
            config(['runway.resources' => array_merge(
                config('rapidez-statamic.runway.resources'),
                config('runway.resources')
            )]);
        }

        return $this;
    }

    public function bootComposers() : self
    {
        if (config('rapidez-statamic.fetch.product')) {
            View::composer('rapidez::product.overview', function (RenderedView $view) {
                $entry = Entry::query()
                    ->where('collection', 'products')
                    ->where('site', config('rapidez.store_code'))
                    ->where('linked_product', config('frontend.product.sku'))
                    ->first();

                $view->with('content', $entry);
            });
        }

        if (config('rapidez-statamic.fetch.category')) {
            View::composer('rapidez::category.overview', function (RenderedView $view) {
                $entry = Entry::query()
                    ->where('collection', 'categories')
                    ->where('site', config('rapidez.store_code'))
                    ->where('linked_category', config('frontend.category.entity_id'))
                    ->first();

                $view->with('content', $entry);
            });
        }

        $this->app->singleton(StatamicGlobalDataComposer::class);
        View::composer('*', StatamicGlobalDataComposer::class);

        return $this;
    }

    public function bootBladeDirectives() : self
    {
        Blade::directive('attributes', function (string $expression) {
            return "<?php echo (new \Illuminate\View\ComponentAttributeBag)($expression); ?>";
        });

        Blade::directive('return', function () {
            return "<?php return; ?>";
        });

        Blade::directive('includeFirstSafe', function (string $expression) {
            $expression = Blade::stripParentheses($expression);

            return "<?php try { echo \$__env->first({$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); } catch (\InvalidArgumentException \$e) { if (!app()->environment('production')) { echo '<hr>'.__('View not found: :view', ['view' => implode(', ', [{$expression}][0])]).'<hr>'; } } ?>";
        });

        return $this;
    }

    public function bootPublishables() : self
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
            __DIR__.'/../config/rapidez-statamic.php' => config_path('rapidez-statamic.php'),
        ], 'config');

        return $this;
    }
}
