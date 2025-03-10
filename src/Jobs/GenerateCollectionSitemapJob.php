<?php

namespace Rapidez\Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Rapidez\Sitemap\Actions\GenerateSitemapAction;
use Statamic\Sites\Site;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;

class GenerateCollectionSitemapJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected Site $site,
        protected Collection $collection,
    ) {}

    public function handle(GenerateSitemapAction $sitemapGenerator): void
    {
        $storeId = $this->site->attribute('magento_store_id');
        $path = trim(config('rapidez-sitemap.path', 'rapidez-sitemaps'), '/');
        $storageDirectory = $path.'/'.$storeId.'/';
        
        $sitemapPath = $storageDirectory
        . config('rapidez.statamic.sitemap.prefix')
        . 'collection_'
        . $this->collection->handle()
        . '.xml';
        
        $sitemapContent = $sitemapGenerator->createSitemapUrlset($this->generateCollectionSitemap());
        $storageDisk = Storage::disk(config('rapidez-sitemap.disk', 'public'));
        $storageDisk->put($sitemapPath, $sitemapContent);
    }

    protected function generateCollectionSitemap() : array
    {
        $entries = $this->collection->queryEntries()->where('site', $this->site->handle())->whereStatus('published')->get();
        
        return $entries->map(fn (Entry $entry) => [
            'loc' => $entry->absoluteUrl(),
            'lastmod' => $entry->lastModified()
        ])->toArray();
    }
}
