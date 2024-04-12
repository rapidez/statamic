<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Support\Facades\DB;
use Statamic\Facades\Site;
use Illuminate\Console\Command;
use Rapidez\Statamic\Models\Brand;
use TorMorten\Eventy\Facades\Eventy;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Asset;
use Rapidez\Statamic\Actions\StatamicEntryAction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

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
                $extraData = [];

                if (Schema::hasTable('amasty_amshopby_option_setting')) {
                    $amastyData = DB::table('amasty_amshopby_option_setting')->where('value', '=', $brand->option_id)->first();
                    $extraData['meta_title'] = $amastyData?->meta_title;
                    $extraData['meta_description'] = $amastyData?->meta_description;

                    if ($amastyData?->image && $file = @fopen(config('rapidez.media_url').'/amasty/shopby/option_images/'.$amastyData->image, 'r')) {
                        Storage::disk('assets')->put('brands/' . $amastyData->image, $file);
                        $extraData['image'] = 'brands/' . $amastyData->image;
                    }
                }

                $statamicEntryAction::createEntry(
                    [
                        'collection' => config('rapidez.statamic.import.brands.collection', 'brands'),
                        'blueprint' => config('rapidez.statamic.import.brands.blueprint', 'brand'),
                        'site' => $site->handle(),
                        'linked_brand' => $brand->option_id,
                    ],
                    array_merge([
                        'locale' => $site->handle(),
                        'site' => $site->handle(),
                        ...$extraData,
                    ], ...[Eventy::filter('rapidez.statamic.brand.entry.data', $brand)])
                );
            }

            $bar->advance();
        }

        $bar->finish();

        return Command::SUCCESS;
    }
}
