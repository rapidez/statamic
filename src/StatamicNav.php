<?php

namespace Rapidez\Statamic;

use Illuminate\Support\Facades\Cache;
use Statamic\Statamic;
use Statamic\Eloquent\Entries\Entry;
use Rapidez\Core\Facades\Rapidez;
use Statamic\Structures\Page;

class StatamicNav
{
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

        return $this->buildTree($nav);
    }

    private function buildTree(array $items): array
    {
        $branch = [];

        foreach ($items as $item) {
            if ($item['children']) {
                $children = $this->buildTree($item['children']);
                if ($children) {
                    $item['children'] = $children;
                }
            }

            $item['url'] = $this->determineEntryUrl($item['entry_id']->augmentable());
            
            $branch[] = $item;
        }

        return $branch;
    }

    public function determineEntryUrl(Entry|Page $entry, string $nav = 'global-link'): string
    {
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


        return '/' . $entry->{$linkedRunwayResourceKey}['url_path'] . $suffix;
    }
}
