<?php

namespace Rapidez\Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Collection as StatamicCollection;
use Statamic\Facades\Taxonomy as TaxonomyFacade;
use Statamic\Sites\Site;
use Statamic\Entries\Collection;
use TorMorten\Eventy\Facades\Eventy;
use Statamic\Support\Str;

class GenerateStoreSitemapJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected Site $site
    ) {}

    public function handle(): void
    {
        $this->createCollectionSitemaps();
        $this->createTaxonomySitemaps();
        $this->addSitemapFilter();
    }

    protected function createCollectionSitemaps() : void
    {
        $collections = StatamicCollection::all();
        $collections = $collections->filter(fn (Collection $collection) => $collection->route($this->site->handle()));

        foreach ($collections as $collection) {
            /* @var Collection $collection */
            if ($collection->queryEntries()->where('site', $this->site->handle())->whereStatus('published')->count()) {
                GenerateCollectionSitemapJob::dispatchSync($this->site, $collection);
            }
        }
    }

    protected function createTaxonomySitemaps() : void
    {
        $taxonomies = TaxonomyFacade::all()
            ->filter(function ($taxonomy) {
                $terms = $taxonomy->queryTerms()
                    ->where('site', $this->site->handle())
                    ->get();

                return $terms
                    ->filter(function ($term) {
                        return view()->exists($term->template());
                    })->isNotEmpty();
            });

        foreach ($taxonomies as $taxonomy) {
            /* @var Taxonomy $taxonomy */
            GenerateTermSitemapJob::dispatchSync($this->site, $taxonomy);
        }
    }

    protected function addSitemapFilter() : void
    {
        $storeId = $this->site->attribute('magento_store_id');
        $storageDisk = Storage::disk(config('rapidez-sitemap.disk', 'public'));
        $path = trim(config('rapidez-sitemap.path', 'rapidez-sitemaps'), '/');
        $storageDirectory = $path.'/'.$storeId.'/';

        $sitemapPrefix = config('rapidez.statamic.sitemap.prefix');

        $sitemaps = collect($storageDisk->files($storageDirectory))
            ->filter(fn($item) => str_starts_with($item, $storageDirectory . $sitemapPrefix))
            ->map(fn($item) => [
                'loc' => Str::ensureRight($this->site->url(), '/') . 'storage/' . $item,
                'lastmod' => $storageDisk->lastModified($item)
                    ? date('Y-m-d H:i:s', $storageDisk->lastModified($item))
                    : null
            ])
            ->toArray();

        Eventy::addFilter('rapidez.sitemap.' . $storeId, fn($rapidezSitemaps) => array_merge($rapidezSitemaps, $sitemaps));
    }
}
