<?php

namespace Rapidez\Statamic\Repositories;

use Statamic\Facades\Entry;
use Statamic\Entries\Entry as StatamicEntry;
use Statamic\Stache\Repositories\EntryRepository as StatamicEntryRepository;

class EntryRepository extends StatamicEntryRepository
{
    public function updateOrCreate(array $data, string $collection, string $identifier) : StatamicEntry
    {
        $entry = Entry::whereCollection($collection)->where($identifier, $data[$identifier])->first();

        if ($entry) {
            $entry->data($data)
                ->save();
            
            return $entry;
        }

        $entry = Entry::make()
                ->collection($collection)
                ->blueprint($collection)
                ->data($data);

        $entry->save();

        return $entry;
    }
}
