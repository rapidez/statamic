<?php

namespace Rapidez\Statamic\Actions;

use Illuminate\Support\Facades\DB;
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
            'locale' => $this->getLocaleFromStore($store->store_id),
        ])->each(fn ($store) => Entry::updateOrCreate($store, 'stores', 'store_id'));
    }

    public function getLocaleFromStore(int $storeId): string
    {
        $store = DB::table('core_config_data')
            ->whereIn('scope_id', [$storeId, 0])
            ->where('path', 'general/locale/code')
            ->orderByDesc('scope_id')
            ->get()
            ->firstOrFail();
        
        return $store->value;
    }
}
