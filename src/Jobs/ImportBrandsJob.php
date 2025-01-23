<?php

namespace Rapidez\Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Rapidez\Statamic\Contracts\ImportsBrands;

class ImportBrandsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function handle(ImportsBrands $importsBrands): void
    {
        $importsBrands->handle();
    }
}