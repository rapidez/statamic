<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Rapidez\Statamic\Jobs\ImportProductsJob;

class SyncProductsCommand extends Command
{
    protected $signature = 'rapidez:statamic:sync:products {store?} {--updated-at=}';

    protected $description = 'Sync the Magento products to Statamic.';

    public function handle(): int
    {
        /** @var ?string $store */
        $store = $this->argument('store');

        /** @var ?string $updatedAt */
        $updatedAt = $this->option('updated-at');

        if($updatedAt !== null) {
            $updatedAt = Carbon::parse($updatedAt);
        }

        ImportProductsJob::dispatch($updatedAt, $store);

        return static::SUCCESS;
    }
}
