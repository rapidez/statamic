<?php

namespace Rapidez\Statamic;

use ArrayAccess;
use Illuminate\Support\Facades\Cache;
use Rapidez\Statamic\Exceptions\NavException;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Link\ArrayableLink;
use Statamic\Statamic;

class RapidezStatamic
{
    public static function getCurrentStoreId(): string
    {
        return once(fn() => (Statamic::isCpRoute()
            ? (SiteFacade::selected()->attributes['magento_store_id'] ?? '1')
            : (SiteFacade::current()->attributes['magento_store_id'] ?? '1')
        ));
    }

    public function nav(string $tag): array
    {
        throw_unless(
            str($tag)->startsWith('nav:'),
            NavException::class,
            'You can only use a nav tag to get a navigation tree.'
        );

        return Cache::rememberForever($tag . '-tree-' . config('rapidez.store'), fn() => $this->buildMenu($tag));
    }

    protected function buildMenu(string $key): array
    {
        return $this->buildTree(Statamic::tag($key)->fetch());
    }

    protected function buildTree(array $items): array
    {
        $tree = [];

        foreach ($items as $item) {
            if ($item['children']) {
                $children = $this->buildTree($item['children']);
                if ($children) {
                    $item['children'] = $children;
                }
            }

            $item['url'] = $this->resolveUrl($item);

            $tree[] = $item;
        }

        return $tree;
    }

    protected function resolveUrl(array $item): string
    {
        foreach (config('rapidez.statamic.navigation.link_fields', ['link', 'link.link']) as $field) {
            $link = $item;

            foreach (explode('.', $field) as $key) {
                $link = $link instanceof Value ? $link->value() : $link;
                $link = is_array($link) || $link instanceof ArrayAccess ? ($link[$key] ?? null) : null;
            }

            $link = $link instanceof Value ? $link->value() : $link;

            if ($link instanceof ArrayableLink && $url = $link->url()) {
                return (string) $url;
            }
        }

        $url = $item['url'] ?? '';

        return (string) ($url instanceof Value ? $url->value() : $url);
    }
}
