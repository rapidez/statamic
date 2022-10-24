<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Statamic\Facades\Entry;
use Illuminate\Support\Facades\DB;

class SyncStoresCommand extends Command
{
    protected $signature = 'rapidez:statamic:sync:stores';

    protected $description = 'Sync the Magento stores to Statamic.';

    public function handle()
    {
        $this->call('cache:clear');
        $stores = DB::table('store')->whereNot('store_id', 0)->get();

        foreach($stores->map(fn ($store) => [
                'store_code' => $store->code,
                'store_id' => $store->store_id,
                'title' => $store->name,
                'locale' => $this->determineLocale($store),
            ]) as $store) {
            Entry::updateOrCreate($store, 'stores', 'store_id');
        }
    }


    public function determineLocale(object $store)
    {
        return match (true) {
            str_contains($store->code, 'uk') => 'en_UK',
            str_contains($store->code, 'nl') => 'nl_NL',
            default => 'en_UK'
        };
    }
}
