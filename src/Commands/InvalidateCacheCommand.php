<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Rapidez\Core\Facades\Rapidez;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\Writer;

class InvalidateCacheCommand extends Command
{
    protected $signature = 'rapidez-statamic:invalidate-cache';

    protected $description = 'Search for invalidatable caches';

    public $urls;

    public $latestCheck;

    public $staticCachePath;

    private ?array $storeConfig = null;

    public function handle(Cacher $cacher, Writer $writer): void
    {
        $stores = Rapidez::getStores();

        foreach ($stores as $store) {
            $staticCachePathConfig = 'statamic.static_caching.strategies.full.path';

            if (in_array($store['code'], config('rapidez.statamic.disabled_sites'))) {
                continue;
            }

            Rapidez::setStore($store);

            if (is_array(config('statamic.static_caching.strategies.full.path'))) {
                $staticCachePathConfig = $staticCachePathConfig . '.' . $store['code'];
            }

            $this->staticCachePath = config($staticCachePathConfig);

            $this->latestCheck = $this->getLatestCheckDate();

            if (!$this->latestCheck) {
                $this->info('Cleared all urls (as we do not have a latest check date yet)');
                $cacher->flush();
                $this->setLatestCheckDate();
                continue;
            }
            $this->setLatestCheckDate();

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

        $this->saveInvalidateConfig();
    }

    protected function addProductsUrls(): self
    {
        $productIds = config('rapidez.models.product')::query()
            ->where('updated_at', '>=', $this->latestCheck)
            ->orWhereIn('entity_id', $this->getUpdatedStockProducts())
            ->pluck('entity_id');

        $parentIds = config('rapidez.models.product_super_link')::whereIn('product_id', $productIds)
            ->groupBy('parent_id')
            ->pluck('parent_id');

        $allIds = $productIds->merge($parentIds)->unique();

        $rewrites = config('rapidez.models.rewrite')::whereIn('entity_id', $allIds)
            ->where('entity_type', 'product')
            ->pluck('request_path');

        $this->addUrls($rewrites);

        return $this;
    }

    protected function getUpdatedStockProducts()
    {
        $currentStock = DB::table('cataloginventory_stock_item')
            ->pluck('qty', 'product_id')
            ->toArray();

        $previousStock = $this->getPreviousStock();

        $this->setPreviousStock($currentStock);

        return array_keys(
            array_diff_assoc(
                (array) $currentStock,
                (array) $previousStock
            ) + array_diff_assoc(
                (array) $previousStock,
                (array) $currentStock
            )
        );
    }

    protected function addCategoryUrls(): self
    {
        $categories = config('rapidez.models.category')::query()
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

    protected function getLatestCheckDate(): ?string
    {
        return Arr::get($this->getInvalidateConfig(), config('rapidez.store_code') . '.last-invalidation');
    }

    protected function setLatestCheckDate(): void
    {
        Arr::set($this->getInvalidateConfig(), config('rapidez.store_code') . '.last-invalidation', DB::selectOne('SELECT NOW() AS `current_time`')->current_time);
    }

    /**
     * @return ?array<int,float>
     */
    protected function getPreviousStock(): ?array
    {
        return Arr::get($this->getInvalidateConfig(), config('rapidez.store_code') . '.product-stocks');
    }

    protected function setPreviousStock(array $previousStock): void
    {
        Arr::set($this->getInvalidateConfig(), config('rapidez.store_code') . '.product-stocks', $previousStock);
    }

    protected function &getInvalidateConfig(): array
    {
        try {
            $this->storeConfig ??= json_decode(Storage::disk('local')->get('invalidate-cache-config.json') ?? '[]', true, 512, JSON_THROW_ON_ERROR);

            return $this->storeConfig;
        } catch (\JsonException $e) {
            $this->storeConfig ??= [];

            return $this->storeConfig;
        }
    }

    protected function saveInvalidateConfig(): void
    {
        Storage::disk('local')->put('invalidate-cache-config.json', json_encode($this->storeConfig ?? []));
    }
}
