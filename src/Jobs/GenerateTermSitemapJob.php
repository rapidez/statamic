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

    protected GenerateSitemapAction $sitemapGenerator;

    public function __construct(
        protected Site $site,
        protected Taxonomy $taxonomy,
    ) {
        $this->sitemapGenerator = new (GenerateSitemapAction::class);
    }

    public function handle(): void
    {
        $sitemap = $this->sitemapGenerator->createSitemap($this->generateTermSitemap());
        Storage::disk('public')->put('sitemap_statamic_taxonomy_' . $this->site->handle() . '_' . $this->taxonomy->handle() . '.xml', $sitemap);
    }

    protected function generateTermSitemap() : string
    {
        $sitemap = '';
        $terms = $this->publishedTerms();

        foreach ($terms as $term) {
            /* @var LocalizedTerm $term */
            $sitemap .= $this->sitemapGenerator->addUrl($term->absoluteUrl(), $term->lastModified());
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
