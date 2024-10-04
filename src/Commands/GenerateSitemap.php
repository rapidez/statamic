<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Sites\Site;
use Statamic\Entries\Collection;
use Statamic\Eloquent\Entries\Entry;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Facades\Taxonomy as TaxonomyFacade;
use Statamic\Taxonomies\Taxonomy;
use Statamic\Taxonomies\LocalizedTerm;

class GenerateSitemap extends Command
{
    protected $signature = 'rapidez:statamic:generate:sitemap';

    protected $description = 'Generate a sitemap based on Statamic collections & taxonomies';


    public function handle()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach (SiteFacade::all() as $site) {
            $sitemap .= $this->addSitemap('sitemap_statamic_' . $site->handle() . '.xml');
            $this->generateStoreSitemap($site);
        }

        $sitemap .= '</sitemapindex>';

        file_put_contents(public_path('statamic_sitemap.xml'), $sitemap);

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
                $this->generateCollectionSitemap($site, $collection);
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
            $this->generateTaxonomySitemap($site, $taxonomy);
        }

        $sitemap .= '</sitemapindex>';
        file_put_contents(public_path('sitemap_statamic_' . $site->handle() . '.xml'), $sitemap);
    }

    protected function generateCollectionSitemap(Site $site, Collection $collection) : void
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
        $entries = $collection->queryEntries()->where('site', $site->handle())->whereStatus('published')->get();

        foreach ($entries as $entry) {
            /* @var Entry $entry */
            $sitemap .= $this->addUrl($entry->url(), $entry->lastModified());
        }

        $sitemap .= '</urlset>';
        file_put_contents(public_path('sitemap_statamic_collection_' . $site->handle() . '_' . $collection->handle() . '.xml'), $sitemap);
    }

    protected function generateTaxonomySitemap(Site $site, Taxonomy $taxonomy) : void
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
        $sitemap .= $this->generateTermSitemap($site);
        $sitemap .= '</urlset>';

        file_put_contents(public_path('sitemap_statamic_taxonomy_' . $site->handle() . '_' . $taxonomy->handle() . '.xml'), $sitemap);
    }

    protected function generateTermSitemap(Site $site) : string
    {
        $sitemap = '';
        $terms = $this->publishedTerms($site);

        foreach ($terms as $term) {
            /* @var LocalizedTerm $term */
            $sitemap .= $this->addUrl($term->absoluteUrl(), $term->lastModified());
        }

        return $sitemap;
    }

    protected function publishedTerms(Site $site)
    {
        return TaxonomyFacade::all()
            ->flatMap(fn ($taxonomy) => $taxonomy->queryTerms()->where('site', $site->handle())->get())
            ->filter
            ->published()
            ->filter(function ($term) {
                return view()->exists($term->template());
            });
    }

    protected function addSitemap($url = null) : string
    {
        $sitemap = '<sitemap>';

        if ($url ?? false) {
            $sitemap .= '<loc>' . url($url) . '</loc>';
        }

        $sitemap .= '</sitemap>';

        return $sitemap;
    }

    protected function addUrl($url = null, $lastmod = null) : string
    {
        $sitemap = '<url>';

        if ($url ?? false) {
            $sitemap .= '<loc>' . url($url) . '</loc>';
        }

        if ($lastmod ?? false) {
            $sitemap .= '<lastmod>' . substr($lastmod, 0, 10) . '</lastmod>';
        }

        $sitemap .= '</url>';

        return $sitemap;
    }
}
