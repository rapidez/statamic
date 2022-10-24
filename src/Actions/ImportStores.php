<?php

namespace Rapidez\Statamic\Actions;

use Illuminate\Support\Facades\DB;
use Statamic\Facades\Entry;
use Rapidez\Statamic\Actions\CreateStores;
use Rapidez\Statamic\Events\StoresImportedEvent;

class ImportStores
{
    public function __construct(
        public CreateStores $createStores
    ) {
    }

    public function import(
        ?string $store = null
    ): void
    {
        $stores = DB::table('store')
            ->join()
            ->whereNot('store_id', 0)->get();
        $this->createStores->create($stores);
        StoresImportedEvent::dispatch();
    }
}
