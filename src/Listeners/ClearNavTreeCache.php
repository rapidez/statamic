<?php

namespace Rapidez\Statamic\Listeners;

use Illuminate\Support\Facades\Cache;
use Statamic\Events\NavTreeSaved;
use Statamic\Eloquent\Structures\NavTree;
use Statamic\Facades\Site;

class ClearNavTreeCache
{
    public function handle(NavTreeSaved $event): void
    {
        /** @var NavTree $tree */
        $tree = $event->tree;
        
        $storeId = Site::get($tree->locale())?->attributes['magento_store_id'] ?? config('rapidez.store');

        Cache::forget('nav:' . $tree->handle() . '-tree-' . $storeId);
        Cache::forget('nav:' . $tree->handle() . '-' . $storeId);
        Cache::driver('array')->forget('global-link' . '-' . $storeId);
    }
}
