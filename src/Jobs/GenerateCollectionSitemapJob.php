<?php

namespace Rapidez\Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Rapidez\Sitemap\Actions\GenerateSitemapAction;
use Statamic\Eloquent\Entries\Entry;
use Statamic\Sites\Site;
use Statamic\Entries\Collection;

class GenerateCollectionSitemapJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected Site $site,
        protected Collection $collection,
    ) {}

    public function handle(GenerateSitemapAction $sitemapGenerator): void
    {
        $sitemapContent = $sitemapGenerator->createSitemap($this->generateCollectionSitemap($sitemapGenerator));

        $sitemapPath = config('rapidez.statamic.sitemap.storage_directory')
            . config('rapidez.statamic.sitemap.prefix')
            . $this->site->attribute('magento_store_id')
            . '_collection_'
            . $this->collection->handle()
            . '.xml';

        Storage::disk('public')->put($sitemapPath, $sitemapContent);
    }


    protected function generateCollectionSitemap(GenerateSitemapAction $sitemapGenerator) : string
    {
        $sitemap = '';
        $entries = $this->collection->queryEntries()->where('site', $this->site->handle())->whereStatus('published')->get();

        foreach ($entries as $entry) {
            /* @var Entry $entry */
            $sitemap .= $sitemapGenerator->addUrl($entry->url(), $entry->lastModified());
        }

        return $sitemap;
    }
}
