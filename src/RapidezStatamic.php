<?php

namespace Rapidez\Statamic;

use Illuminate\Support\Facades\Cache;
use Rapidez\Statamic\Exceptions\NavException;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Group;
use Statamic\Fieldtypes\Link;
use Statamic\Statamic;

class RapidezStatamic
{
    protected array $resolvedLinks = [];

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
        $this->resolvedLinks = [];

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
        foreach ($item as $value) {
            if ($url = $this->linkUrl($value)) {
                return $url;
            }
        }

        $url = $item['url'] ?? '';

        return (string) ($url instanceof Value ? $url->value() : $url);
    }

    protected function linkUrl(mixed $value): ?string
    {
        if (! $value instanceof Value || ! $value->raw()) {
            return null;
        }

        if ($value->fieldtype() instanceof Group) {
            foreach ($value->value() as $groupValue) {
                if ($url = $this->linkUrl($groupValue)) {
                    return $url;
                }
            }

            return null;
        }

        if (! $value->fieldtype() instanceof Link) {
            return null;
        }

        return $this->resolvedLinks[$value->raw()] ??= $value->value()?->url() ?: null;
    }
}
