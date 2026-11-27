<?php

namespace Rapidez\Statamic\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Rapidez\Core\Facades\Rapidez;
use Statamic\Facades\Site;

class SetStoreFromSelectedSite
{
    public function handle(Request $request, Closure $next)
    {
        $site = Site::selected();

        if ($site) {
            $storeId = $site->attribute('magento_store_id');

            if ($storeId) {
                Rapidez::setStore($storeId);

                // Make sure the root url stays the same.
                URL::forceRootUrl($request->root());
                config()->set('frontend.base_url', url('/'));
            }
        }

        return $next($request);
    }
}
