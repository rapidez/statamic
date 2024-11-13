<?php

namespace Rapidez\Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Rapidez\Statamic\Actions\GenerateSitemapAction;
use Statamic\Eloquent\Entries\Entry;
use Statamic\Sites\Site;
use Statamic\Entries\Collection;

class GenerateCollectionSitemapJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected GenerateSitemapAction $sitemapGenerator;

    public function __construct(
        protected Site $site,
        protected Collection $collection,
    ) {
        $this->sitemapGenerator = new (GenerateSitemapAction::class);
    }

    public function handle(): void
    {
        $sitemapContent = $this->sitemapGenerator->createSitemap($this->generateCollectionSitemap());

        $sitemapPath = config('rapidez.statamic.sitemap.storage_directory')
            . config('rapidez.statamic.sitemap.prefix')
            . $this->site->attribute('magento_store_id')
            . '_collection_'
            . $this->collection->handle()
            . '.xml';

        Storage::disk('public')->put($sitemapPath, $sitemapContent);
    }


    protected function generateCollectionSitemap() : string
    {
        $sitemap = '';
        $entries = $this->collection->queryEntries()->where('site', $this->site->handle())->whereStatus('published')->get();

        foreach ($entries as $entry) {
            /* @var Entry $entry */
            $sitemap .= $this->sitemapGenerator->addUrl($entry->url(), $entry->lastModified());
        }

        return $sitemap;
    }
}
