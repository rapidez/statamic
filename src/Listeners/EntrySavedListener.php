<?php

namespace Rapidez\Statamic\Listeners;

use Illuminate\Support\Facades\Cache;
use Statamic\Events\EntrySaved;
use Statamic\Entries\Entry;

class EntrySavedListener
{
    public function handle(EntrySaved $event): void
    {
        /** @var Entry $entry */
        $entry = $event->entry;

        if ($entry->collectionHandle() !== 'products') {
            return;
        }

        $siteHandle = $entry->locale();
        $sku = $entry->get('sku');

        Cache::rememberForever('statamic-product-' . $sku . '-' . $siteHandle, fn() => $entry);
    }
}
