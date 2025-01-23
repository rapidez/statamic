<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Rapidez\Statamic\Jobs\ImportBrandsJob;

class ImportBrands extends Command
{
    protected $signature = 'rapidez:statamic:import:brands';

    protected $description = 'Create a new brand entry for a brand if it does not exist.';

    public function handle(): int
    {
        ImportBrandsJob::dispatch();

        return Command::SUCCESS;
    }
}
