<?php

namespace Rapidez\Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Rapidez\Statamic\Actions\GenerateSitemapAction;
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

    public function handle(GenerateSitemapAction $sitemapGenerator): void
    {
        $sitemapContent = $sitemapGenerator->createSitemap($this->generateTermSitemap($sitemapGenerator));

        $sitemapPath = config('rapidez.statamic.sitemap.storage_directory')
            . config('rapidez.statamic.sitemap.prefix')
            . $this->site->attribute('magento_store_id')
            . '_taxonomy_'
            . $this->taxonomy->handle()
            . '.xml';

        Storage::disk('public')->put($sitemapPath, $sitemapContent);
    }


    protected function generateTermSitemap(GenerateSitemapAction $sitemapGenerator) : string
    {
        $sitemap = '';
        $terms = $this->publishedTerms();

        foreach ($terms as $term) {
            /* @var LocalizedTerm $term */
            $sitemap .= $sitemapGenerator->addUrl($term->absoluteUrl(), $term->lastModified());
        }

        return $sitemap;
    }

    protected function publishedTerms()
    {
        return TaxonomyFacade::all()
            ->flatMap(fn ($taxonomy) => $taxonomy->queryTerms()
                ->where('site', $this->site->handle())
                ->whereNotNull('uri')
                ->get()
            )
            ->filter
            ->published()
            ->filter(function ($term) {
                return view()->exists($term->template());
            });
    }
}
