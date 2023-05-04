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

        if ($entry->collectionHandle() === 'products') {
            $this->clearProduct($entry);
        } else if ($entry->collectionHandle() === 'categories') {
            $this->clearCategory($entry);
        }
    }

    protected function clearProduct(Entry $entry)
    {
        $siteHandle = $entry->locale();
        $sku = $entry->get('sku');

        Cache::forever('statamic-product-' . $sku . '-' . $siteHandle, $entry);
    }

    protected function clearCategory(Entry $entry)
    {
        $siteHandle = $entry->locale();
        $entityId = $entry->get('magento_entity_id');

        Cache::forever('statamic-category-' . $entityId . '-' . $siteHandle, $entry);
    }
}
