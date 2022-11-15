<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Rapidez\Statamic\Jobs\ImportCategoriesJob;

class SyncCategoriesCommand extends Command
{
    protected $signature = 'rapidez:statamic:sync:categories';

    protected $description = 'Sync the Magento categories to Statamic.';

    public function handle(): int
    {
        ImportCategoriesJob::dispatch();

        return static::SUCCESS;
    }
}
