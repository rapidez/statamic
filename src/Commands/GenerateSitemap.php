<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Rapidez\Statamic\Jobs\GenerateCollectionSitemapJob;
use Rapidez\Statamic\Jobs\GenerateTermSitemapJob;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Sites\Site;
use Statamic\Entries\Collection;
use Statamic\Facades\Taxonomy as TaxonomyFacade;
use Statamic\Taxonomies\Taxonomy;

class GenerateSitemap extends Command
{
    protected $signature = 'rapidez:statamic:generate:sitemap';

    protected $description = 'Generate a sitemap based on Statamic collections & taxonomies';


    public function handle()
    {
        foreach (SiteFacade::all() as $site) {
            $this->generateStoreSitemap($site);
        }

        return Command::SUCCESS;
    }

    protected function generateStoreSitemap(Site $site) : void
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $collections = CollectionFacade::all();
        $collections = $collections->filter(fn (Collection $collection) => $collection->route($site->handle()));

        foreach ($collections as $collection) {
            /* @var Collection $collection */
            if ($collection->queryEntries()->where('site', $site->handle())->whereStatus('published')->count()) {
                $sitemap .= $this->addSitemap('sitemap_statamic_collection_' . $site->handle() . '_' . $collection->handle() . '.xml');
                GenerateCollectionSitemapJob::dispatch($site, $collection);
            }
        }

        $taxonomies = TaxonomyFacade::all()
            ->filter(function ($taxonomy) use ($site) {
                $terms = $taxonomy->queryTerms()
                    ->where('site', $site->handle())
                    ->get();

                return $terms
                    ->filter(function ($term) {
                        return view()->exists($term->template());
                    })->isNotEmpty();
            });

        foreach ($taxonomies as $taxonomy) {
            /* @var Taxonomy $taxonomy */
            $sitemap .= $this->addSitemap('sitemap_statamic_taxonomy_' . $site->handle() . '_' . $taxonomy->handle() . '.xml');
            GenerateTermSitemapJob::dispatch($site, $taxonomy);
        }

        $sitemap .= '</sitemapindex>';
        file_put_contents(public_path('sitemap_statamic_' . $site->handle() . '.xml'), $sitemap);
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
