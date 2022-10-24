<?php

namespace Rapidez\Statamic\Actions;

use Statamic\Facades\Entry;
use Illuminate\Support\Collection;

class CreateStores
{
    public function create(Collection $stores): void
    {
        $stores->map(fn ($store) => [
            'store_code' => $store->code,
            'store_id' => $store->store_id,
            'title' => $store->name,
            'locale' => $this->determineLocale($store),
        ])->each(fn ($store) => Entry::updateOrCreate($store, 'stores', 'store_id'));
    }

    public function determineLocale(object $store)
    {
        return match (true) {
            str_contains($store->code, 'nl') => 'nl_NL',
            default => 'en_UK'
        };
    }
}
