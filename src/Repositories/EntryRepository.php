<?php

namespace Rapidez\Statamic\Repositories;

use Statamic\Facades\Entry;
use Statamic\Entries\Entry as StatamicEntry;
use Statamic\Stache\Repositories\EntryRepository as StatamicEntryRepository;

class EntryRepository extends StatamicEntryRepository
{
    public function updateOrCreate(array $product) : StatamicEntry
    {
        $entry = Entry::whereCollection('products')->where('sku', $product['sku'])->first();

        if ($entry) {
            $entry->data($product)->save();
            $entry->publish();
            return $entry;
        }

        $entry = Entry::make()
                ->collection('products')
                ->blueprint('products')
                ->data($product);
        $entry->save();

        return $entry;
    }
}
