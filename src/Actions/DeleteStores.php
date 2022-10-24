<?php

namespace Rapidez\Statamic\Actions;

use Statamic\Facades\Entry;
use Illuminate\Support\Collection;

class DeleteStores
{
    public function delete(Collection $stores): void
    {
        if (!$stores) {
            return;
        }

        $magentoStoreIds = $stores->map(fn ($store) => $store->store_id);
        $statamicStoreIds = Entry::whereCollection('stores')->map(fn ($entry) => $entry->store_id);

        $entries = Entry::query()->where('collection', 'stores')->whereIn('store_id', $statamicStoreIds->diff($magentoStoreIds)->toArray())->get();
        $entries->each(fn ($entry) => $entry->delete());
    }
}
