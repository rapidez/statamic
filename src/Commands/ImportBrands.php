<?php

namespace Rapidez\Statamic\Commands;

use Statamic\Facades\Site;
use Illuminate\Console\Command;
use Rapidez\Statamic\Models\Brand;
use Rapidez\Statamic\Actions\StatamicEntryAction;
use Illuminate\Support\Facades\Event;

class ImportBrands extends Command
{
    protected $signature = 'rapidez:statamic:import:brands {--site=* : Sites handles or urls to process, if none are passed it will be done for all sites (Default will be all sites)}';

    protected $description = 'Create a new brand entry for a brand if it does not exist.';
    
    public function handle(StatamicEntryAction $statamicEntryAction): int
    {
        $sites = $this->option('site');

        $sites = $sites
            ? array_filter(array_map(fn($handle) => (Site::get($handle) ?: Site::findByUrl($handle)) ?: $this->output->warning(__('No site found with handle or url: :handle', ['handle' => $handle])), $sites))
            : Site::all();
        
        $bar = $this->output->createProgressBar(count($sites));

        $bar->start();
        
        foreach ($sites as $site) {
            $bar->display();
            
            foreach (Brand::get() as $brand) {
                $statamicEntryAction::createEntry(
                    [
                        'collection' => config('rapidez-statamic.import.brands.collection', 'brands'),
                        'blueprint' => config('rapidez-statamic.import.brands.blueprint', 'brand'),
                        'site' => $site->handle(),
                        'linked_brand' => $brand->option_id,
                    ],
                    array_merge([
                        'locale' => $site->handle(),
                        'site' => $site->handle(),
                    ], ...Event::dispatch('rapidez-statamic:brand-entry-data', ['brand' => $brand]))
                );
            }
            $bar->advance();
        }

        $bar->finish();

        return Command::SUCCESS;
    }
}
