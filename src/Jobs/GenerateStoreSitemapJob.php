<?php

namespace Rapidez\Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Facades\Collection as StatamicCollection;
use Statamic\Facades\Taxonomy as TaxonomyFacade;
use Statamic\Sites\Site;
use Statamic\Entries\Collection;
use Statamic\Taxonomies\Taxonomy;

class GenerateStoreSitemapJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected Site $site
    ) {}

    public function handle(): void
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $collections = StatamicCollection::all();
        $collections = $collections->filter(fn (Collection $collection) => $collection->route($this->site->handle()));

        foreach ($collections as $collection) {
            /* @var Collection $collection */
            if ($collection->queryEntries()->where('site', $this->site->handle())->whereStatus('published')->count()) {
                $sitemap .= $this->addSitemap('sitemap_statamic_collection_' . $this->site->handle() . '_' . $collection->handle() . '.xml');
                GenerateCollectionSitemapJob::dispatch($this->site, $collection);
            }
        }

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
            $sitemap .= $this->addSitemap('sitemap_statamic_taxonomy_' . $this->site->handle() . '_' . $taxonomy->handle() . '.xml');
            GenerateTermSitemapJob::dispatch($this->site, $taxonomy);
        }

        $sitemap .= '</sitemapindex>';
        file_put_contents(public_path('sitemap_statamic_' . $this->site->handle() . '.xml'), $sitemap);
    }

    protected function addSitemap($url = null) : string
    {
        $sitemap = '<sitemap>';

        if ($url) {
            $sitemap .= '<loc>' . url($url) . '</loc>';
        }

        $sitemap .= '</sitemap>';

        return $sitemap;
    }
}
