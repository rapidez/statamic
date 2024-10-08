<?php

namespace Rapidez\Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
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

    public function handle(): void
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
        $entries = $this->collection->queryEntries()->where('site', $this->site->handle())->whereStatus('published')->get();

        foreach ($entries as $entry) {
            /* @var Entry $entry */
            $sitemap .= $this->addUrl($entry->url(), $entry->lastModified());
        }

        $sitemap .= '</urlset>';
        file_put_contents(public_path('sitemap_statamic_collection_' . $this->site->handle() . '_' . $this->collection->handle() . '.xml'), $sitemap);
    }

    protected function addUrl($url = null, $lastmod = null) : string
    {
        $sitemap = '<url>';

        if ($url) {
            $sitemap .= '<loc>' . url($url) . '</loc>';
        }

        if ($lastmod) {
            $sitemap .= '<lastmod>' . substr($lastmod, 0, 10) . '</lastmod>';
        }

        $sitemap .= '</url>';

        return $sitemap;
    }
}
