<?php

namespace Rapidez\Statamic\Actions;

use Illuminate\Support\Facades\DB;
use Statamic\Facades\Site;
use Rapidez\Statamic\Models\Brand;
use TorMorten\Eventy\Facades\Eventy;
use Illuminate\Support\Facades\Storage;
use Rapidez\Statamic\Actions\StatamicEntryAction;
use Illuminate\Support\Facades\Schema;
use Rapidez\Statamic\Contracts\ImportsBrands;

class ImportBrands implements ImportsBrands
{
    public function __construct(
        protected StatamicEntryAction $statamicEntryAction
    ) {}

    public function handle()
    {
        foreach (Site::all() as $site) {

            foreach (Brand::get() as $brand) {
                $extraData = [];

                if (Schema::hasTable('amasty_amshopby_option_setting')) {
                    $amastyData = DB::table('amasty_amshopby_option_setting')->where('value', '=', $brand->option_id)->first();
                    $extraData['meta_title'] = $amastyData?->meta_title;
                    $extraData['meta_description'] = $amastyData?->meta_description;

                    if ($amastyData?->image && $file = @fopen(config('rapidez.media_url') . '/amasty/shopby/option_images/' . $amastyData->image, 'r')) {
                        Storage::disk('assets')->put('brands/' . $amastyData->image, $file);
                        $extraData['image'] = 'brands/' . $amastyData->image;
                    }
                }

                $this->statamicEntryAction::createEntry(
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
        }
    }
}