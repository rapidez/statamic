<?php

namespace Rapidez\Statamic\Commands;

use Statamic\Facades\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Rapidez\Core\Facades\Rapidez;
use Rapidez\Core\Models\Page;
use Rapidez\Statamic\Actions\ConvertField;
use Statamic\Eloquent\Entries\EntryQueryBuilder;
use Statamic\Facades\Entry;

class MigrateCmsPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic-content-migration:migrate-cms-pages {--identifiers=} {--identifier-type=whitelist : Type of identifier, whitelist/blacklist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate magento CMS pages into the "pages" collection';

    private ConvertField $converter;

    /** @var Collection<int,\Statamic\Sites\Site> $sitePerStoreId */
    private Collection $sitePerStoreId;

    /**
     * Execute the console command.
     */
    public function handle(ConvertField $converter): void
    {
        $this->converter = $converter;

        $this->sitePerStoreId = Site::all()
            ->filter(fn(\Statamic\Sites\Site $site) => $site->attribute('magento_store_id'))
            ->mapWithKeys(fn(\Statamic\Sites\Site $site, $key): array => [$site->attribute('magento_store_id') => $site]);

        foreach ($this->sitePerStoreId as $storeId => $site) {
            Rapidez::setStore($storeId);

            $this->createCmsPages($site);
        }

        $this->output->writeln(__('Don\'t forget to disable the pages in Magento or remove the cms page fallback route! ":line"', ['line' => 'Rapidez::removeFallbackRoute(CmsPageController::class);']));
    }

    public function createCmsPages(\Statamic\Sites\Site $site): void
    {
        /** @var Page $cmsPageModel */
        $cmsPageModel = config('rapidez.models.page');
        $pagesQuery = $cmsPageModel::query();

        if ($this->option('identifiers')) {
            $identifiers = is_array($this->option('identifiers')) ? $this->option('identifiers') : explode(',', $this->option('identifiers'));
            $pagesQuery->when($this->option('identifier-type') === 'blacklist', fn($q) => $q->whereNotIn('identifier', $identifiers));
            $pagesQuery->when($this->option('identifier-type') !== 'blacklist', fn($q) => $q->whereIn('identifier', $identifiers));
        }

        $this->output->progressStart($pagesQuery->count());

        foreach ($pagesQuery->lazy() as $page) {
            $this->output->progressAdvance();

            /** @var EntryQueryBuilder $query */
            $query = Entry::query();
            if ($query
                ->where('collection', 'pages')
                ->where('slug', $page->identifier)
                ->where('site', $site->handle)
                ->exists()
            ) {
                continue;
            }

            $this->createCmsPage($page, $site);
        }

        $this->output->progressFinish();
    }

    private function createCmsPage($page, \Statamic\Sites\Site $site): void
    {
        if (trim((string) $page->content) === '') {
            return;
        }

        $pageStores = DB::table('cms_page_store')->where('page_id', $page->page_id)->pluck('store_id');

        /** @var EntryQueryBuilder $query */
        $query = Entry::query();
        $query
            ->where('collection', 'pages')
            ->where('slug', $page->identifier);
        if ($page->store_id !== 0) {
            $query->whereIn('site', $this->sitePerStoreId->filter(fn(\Statamic\Sites\Site $aSite): bool => in_array($aSite->attribute('magento_store_id'), $pageStores->toArray()))->map(fn(\Statamic\Sites\Site $aSite) => $aSite->handle()));
        }

        /** @var ?\Statamic\Entries\Entry $originEntry */
        $originEntry = $query->first();

        if ($originEntry !== null) {
            /** @var \Statamic\Entries\Entry $entry */
            $entry = $originEntry->makeLocalization($site->handle);
        } else {
            /** @var \Statamic\Entries\Entry $entry */
            $entry = Entry::make();
            $entry
                ->collection('pages')
                ->locale($site)
                ->published(1)
                ->slug($page->identifier);
        }

        $entry->title = Rapidez::content($page->title); // @phpstan-ignore property.notFound

        $this->converter->execute($entry, $page->content, 'page_builder');

        $entry->seo = [ // @phpstan-ignore property.notFound
            'title' => Rapidez::content($page->meta_title),
            'description' => Rapidez::content($page->meta_description),
        ];

        $entry->save();
    }
}
