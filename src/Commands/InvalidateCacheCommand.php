<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use Rapidez\Core\Facades\Rapidez;
use Statamic\Facades\File;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\Writer;

class InvalidateCacheCommand extends Command
{
    protected $signature = 'rapidez-statamic:invalidate-cache';

    protected $description = 'Search for invalidatable caches';

    public $urls;

    public $latestCheck;

    public function handle(Cacher $cacher, Writer $writer): void
    {
        $this->latestCheck = $this->getLatestCheckDate();

        if (!$this->latestCheck) {
            $this->info('Cleared all urls (as we do not have a latest check date yet)');
            $cacher->flush();
            $this->setLatestCheckDate($writer);
            return;
        }
        $this->setLatestCheckDate($writer);

        $stores = Rapidez::getStores();

        foreach ($stores as $store) {
            Rapidez::setStore($store);

            $this->urls = collect();

            $this
                ->addProductsUrls()
                ->addCategoryUrls()
                ->addPageUrls()
                ->makeAbsolute();

            $cacher->invalidateUrls($this->urls);

            foreach ($this->urls as $url) {
                $this->line($url);
            }

            $this->info('Done invalidating');
        }
    }

    protected function addProductsUrls(): self
    {
        $products = config('rapidez.models.product')::withoutGlobalScopes()
            ->where('updated_at', '>=', $this->latestCheck)
            ->with(['parent:entity_id' => ['rewrites']])
            ->with('rewrites')
            ->get('entity_id');

        foreach ($products as $product) {
            $this->addUrls($this->getUrlsFromRewrites($product->rewrites));

            if ($product->parent) {
                $this->addUrls($this->getUrlsFromRewrites($product->parent->rewrites));
            }
        }

        return $this;
    }

    protected function addCategoryUrls(): self
    {
        $categories = config('rapidez.models.category')::withoutGlobalScopes()
            ->where('updated_at', '>=', $this->latestCheck)
            ->get('entity_id');

        foreach ($categories as $category) {
            $this->addUrls($this->getUrlsFromRewrites($category->rewrites));
        }

        return $this;
    }

    protected function addPageUrls(): self
    {
        $identifiers = config('rapidez.models.page')::query()
            ->where('update_time', '>=', $this->latestCheck)
            ->pluck('identifier');

        $this->addUrls($identifiers);

        return $this;
    }

    protected function addUrls($urls): self
    {
        $this->urls = $this->urls->merge($urls);

        return $this;
    }

    protected function getUrlsFromRewrites($rewrites)
    {
        return $rewrites->map(fn ($rewrite) => $rewrite->request_path);
    }

    protected function makeAbsolute(): self
    {
        $this->urls->transform(fn ($identifier) => url($identifier));

        return $this;
    }

    protected function getLatestCheckDate()
    {
        try {
            return File::get(config('statamic.static_caching.strategies.full.path') . '/.last-invalidation');
        } catch (FileNotFoundException $e) {
            return null;
        }
    }

    protected function setLatestCheckDate(Writer $writer): void
    {
        $writer->write(
            config('statamic.static_caching.strategies.full.path') . '/.last-invalidation',
            // With this we're just making sure the comparison
            // is done within the same timezone in MySQL.
            DB::selectOne('SELECT NOW() AS `current_time`')->current_time
        );
    }
}
