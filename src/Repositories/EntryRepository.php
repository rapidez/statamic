<?php

namespace Rapidez\Statamic\Repositories;

use Statamic\Facades\Entry;
use Statamic\Entries\Entry as StatamicEntry;
use Statamic\Stache\Repositories\EntryRepository as StatamicEntryRepository;

class EntryRepository extends StatamicEntryRepository
{
    public function updateOrCreate(array $data, string $collection, string $identifier, string $storeCode) : StatamicEntry
    {
        $entry = Entry::whereCollection($collection)->where($identifier, $data[$identifier])->where('locale', $storeCode)->first();

        if ($entry) {
            $entry->merge($data)
                ->locale($storeCode)
                ->save();

            return $entry;
        }

        $entry = Entry::make()
                ->collection($collection)
                ->blueprint($collection)
                ->locale($storeCode)
                ->data($data);

        if ($identifier == 'slug' && isset($data[$identifier])) {
            $entry->slug($data[$identifier]);
        }
        
        $entry->save();

        return $entry;
    }
}
