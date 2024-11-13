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
        $collections = StatamicCollection::all();
        $collections = $collections->filter(fn (Collection $collection) => $collection->route($this->site->handle()));

        foreach ($collections as $collection) {
            /* @var Collection $collection */
            if ($collection->queryEntries()->where('site', $this->site->handle())->whereStatus('published')->count()) {
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
            GenerateTermSitemapJob::dispatch($this->site, $taxonomy);
        }
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
