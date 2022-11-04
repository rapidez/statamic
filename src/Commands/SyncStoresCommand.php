<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Rapidez\Statamic\Jobs\ImportStoresJob;
use Statamic\Facades\Entry;
use Illuminate\Support\Facades\DB;

class SyncStoresCommand extends Command
{
    protected $signature = 'rapidez:statamic:sync:stores';

    protected $description = 'Sync the Magento stores to Statamic.';

    public function handle()
    {
        ImportStoresJob::dispatch();

        return static::SUCCESS;
    }
}
