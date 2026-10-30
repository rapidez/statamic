<?php

namespace Rapidez\Statamic\StaticCaching;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Rapidez\Core\Facades\Rapidez;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Contracts\Globals\Variables;
use Statamic\Contracts\Structures\Nav;
use Statamic\Contracts\Structures\NavTree;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Forms\Form;
use Statamic\StaticCaching\DefaultInvalidator;

class CustomInvalidator extends DefaultInvalidator
{
    public function invalidate($item)
    {
        if (
            $item instanceof GlobalSet
            || $item instanceof Variables
            || $item instanceof Nav
            || $item instanceof NavTree
            || $item instanceof Form
            || $this->rules === 'all'
        ) {
            Cache::flush();
            return $this->cacher->flush();
        }

        parent::invalidate($item);
    }

    protected function getItemUrls($item)
    {
        if ($item instanceof Model) {
            return $this->getModelUrls($item);
        }

        return parent::getItemUrls($item);
    }

    // Runway models like products and categories, brands for example don't have a page.
    protected function getModelUrls(Model $model): array
    {
        if (! $model->hasAttribute('url') || ! method_exists($model, 'getSiteHandle')) {
            return [];
        }

        if (! $site = Site::get($model->getSiteHandle())) {
            return [];
        }

        // In the control panel the model has been retrieved within the store of
        // the request domain, so we fetch it again within the store of the site.
        $url = Rapidez::withStore(
            $site->attributes()['magento_store_id'] ?? config('rapidez.store'),
            fn () => $model->newQuery()->whereKey($model->getKey())->first()?->url
        );

        return $url ? [URL::tidy($site->absoluteUrl() . '/' . $url)] : [];
    }
}
