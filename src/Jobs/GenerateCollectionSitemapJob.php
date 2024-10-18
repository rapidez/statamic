<?php

namespace Rapidez\Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
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
        $sitemap = $this->sitemapGenerator->createSitemap($this->generateCollectionSitemap());
        file_put_contents(public_path('sitemap_statamic_collection_' . $this->site->handle() . '_' . $this->collection->handle() . '.xml'), $sitemap);
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
