<?php

namespace Rapidez\Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Rapidez\Sitemap\Actions\GenerateSitemapAction;
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
        $storeId = $this->site->attribute('magento_store_id');
        $path = trim(config('rapidez-sitemap.path', 'rapidez-sitemaps'), '/');
        $storageDirectory = $path.'/'.$storeId.'/';
        
        $sitemapPath = $storageDirectory
        . config('rapidez.statamic.sitemap.prefix')
        . 'taxonomy_'
        . $this->taxonomy->handle()
        . '.xml';

        $sitemapContent = $sitemapGenerator->createSitemapUrlset($this->generateTermSitemap());
        $storageDisk = Storage::disk(config('rapidez-sitemap.disk', 'public'));
        $storageDisk->put($sitemapPath, $sitemapContent);
    }


    protected function generateTermSitemap() : array
    {
        $terms = $this->publishedTerms();
        return $terms->map(fn (LocalizedTerm $term) => [
            'loc' => $term->absoluteUrl(),
            'lastmod' => $term->lastModified()
        ])->toArray();
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
