<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Rapidez\Statamic\Jobs\GenerateStoreSitemapJob;
use Statamic\Facades\Site as SiteFacade;

class GenerateSitemap extends Command
{
    protected $signature = 'rapidez:statamic:generate:sitemap';

    protected $description = 'Generate a sitemap based on Statamic collections & taxonomies';


    public function handle()
    {
        foreach (SiteFacade::all() as $site) {
            GenerateStoreSitemapJob::dispatch($site);
        }

        return Command::SUCCESS;
    }
}
