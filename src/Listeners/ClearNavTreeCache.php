<?php

namespace Rapidez\Statamic\Listeners;

use Illuminate\Support\Facades\Cache;
use Statamic\Events\NavTreeSaved;
use Statamic\Eloquent\Structures\NavTree;

class ClearNavTreeCache
{
    public function handle(NavTreeSaved $event): void
    {
        /** @var NavTree $tree */
        $tree = $event->tree;
        
        $cacheKey = 'nav:' . $tree->handle() . '-' . config('rapidez.store');
        
        Cache::forget($cacheKey);
    }
}
