<?php

namespace Rapidez\Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Facades\Taxonomy as TaxonomyFacade;
use Statamic\Sites\Site;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\Taxonomy;

class GenerateTermSitemapJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        protected Site $site,
        protected Taxonomy $taxonomy,
    ) {}

    public function handle(): void
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
        $sitemap .= $this->generateTermSitemap();
        $sitemap .= '</urlset>';

        file_put_contents(public_path('sitemap_statamic_taxonomy_' . $this->site->handle() . '_' . $this->taxonomy->handle() . '.xml'), $sitemap);
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

    protected function generateTermSitemap() : string
    {
        $sitemap = '';
        $terms = $this->publishedTerms();

        foreach ($terms as $term) {
            /* @var LocalizedTerm $term */
            $sitemap .= $this->addUrl($term->absoluteUrl(), $term->lastModified());
        }

        return $sitemap;
    }

    protected function publishedTerms()
    {
        return TaxonomyFacade::all()
            ->flatMap(fn ($taxonomy) => $taxonomy->queryTerms()->where('site', $this->site->handle())->get())
            ->filter
            ->published()
            ->filter(function ($term) {
                return view()->exists($term->template());
            });
    }
}
