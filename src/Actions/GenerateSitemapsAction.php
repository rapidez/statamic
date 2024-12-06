<?php

namespace Rapidez\Statamic\Actions;

use Statamic\Facades\Site;
use TorMorten\Eventy\Facades\Eventy;
use Illuminate\Support\Facades\Storage;
use Rapidez\Statamic\Jobs\GenerateStoreSitemapJob;

class GenerateSitemapsAction
{
    public static function generate(): void
    {
        Site::all()->each(function ($site) {
            GenerateStoreSitemapJob::dispatchSync($site);
        });
    }
}
