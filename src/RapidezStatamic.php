<?php

namespace Rapidez\Statamic;

use Illuminate\Support\Facades\Cache;
use Statamic\Statamic;
use Statamic\Eloquent\Entries\Entry;
use Rapidez\Core\Facades\Rapidez;
use Statamic\Structures\Page;

class RapidezStatamic
{
    protected array $navCache = [];

    public function nav(string $tag): array
    {
        if (!str($tag)->startsWith('nav:')) {
            return [];
        }

        return Cache::rememberForever($tag . '-' . config('rapidez.store'), fn() => $this->buildMenu($tag));
    }

    protected function buildMenu(string $key): array
    {
        $nav = Statamic::tag($key)->fetch();

        return $this->buildTree($nav, $key);
    }

    private function buildTree(array $items, string $nav): array
    {
        $branch = [];

        foreach ($items as $item) {
            if ($item['children']) {
                $children = $this->buildTree($item['children'], $nav);
                if ($children) {
                    $item['children'] = $children;
                }
            }

            $item['url'] = $this->determineEntryUrl($item['entry_id']->augmentable(), $nav);
            
            $branch[] = $item;
        }

        return $branch;
    }

    public function determineEntryUrl(Entry|Page $entry, string $nav = 'global-link'): string
    {
        $cacheKey = $nav . '-' . config('rapidez.store');
        $this->navCache[$nav] = Cache::driver('array')->rememberForever($cacheKey, fn () => Cache::get($cacheKey, []));
        
        if ( ! isset($this->navCache[$nav][$entry->id()])) {
            $linkedRunwayResourceKey = $entry
                ->data()
                ->keys()
                ->firstWhere(fn($field) => collect([
                    ...config('rapidez.statamic.nav.allowed_collections'),
                    'linked_',
                ])->firstWhere(fn($key) => str($field)->startsWith($key)));

            if (!$linkedRunwayResourceKey || !$entry->{$linkedRunwayResourceKey} || $entry->slug()) {
                return $entry->url() ?? '';
            }

            $suffix = str($linkedRunwayResourceKey)->contains('category')
                ? Rapidez::config('catalog/seo/category_url_suffix', '')
                : (
                    str($linkedRunwayResourceKey)->contains('product')
                    ? Rapidez::config('catalog/seo/product_url_suffix', '')
                    : ''
                );


            $this->navCache[$nav][$entry->id()] = '/' . $entry->{$linkedRunwayResourceKey}['url_path'] . $suffix;

            Cache::store('array')->forever($cacheKey, $this->navCache[$nav]);
        }

        return $this->navCache[$nav][$entry->id()];
    }
}
