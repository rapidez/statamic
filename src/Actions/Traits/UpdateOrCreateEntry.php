<?php

namespace Rapidez\Statamic\Actions\Traits;

use Statamic\Facades\Entry;
use Statamic\Entries\Entry as StatamicEntry;

trait UpdateOrCreateEntry
{
    public function updateOrCreate(array $data, string $collection, string $identifier, string $storeCode) : StatamicEntry
    {
        $entry = Entry::query()
            ->where('collection', $collection)
            ->where($identifier, $data[$identifier])
            ->where('site', $storeCode)
            ->first();

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
