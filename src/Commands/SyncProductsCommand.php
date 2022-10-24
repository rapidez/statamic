<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Rapidez\Statamic\Jobs\ImportProductsJob;

class SyncProductsCommand extends Command
{
    protected $signature = 'rapidez:statamic:sync:products {store?}';

    protected $description = 'Sync the Magento products to Statamic.';

    public function handle(): int
    {
        ImportProductsJob::dispatch($this->argument('store'));

        return static::SUCCESS;
    }
}
