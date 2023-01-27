<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Rapidez\Statamic\Jobs\DeleteProductsJob;

class DeleteProductsCommand extends Command
{
    protected $signature = 'rapidez:statamic:delete:products {store?}';

    protected $description = 'Delete products that only exist in Statamic.';

    public function handle(): int
    {
        /** @var ?string $store */
        $store = $this->argument('store');

        DeleteProductsJob::dispatch($store);

        return static::SUCCESS;
    }
}
