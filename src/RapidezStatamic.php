<?php

namespace Rapidez\Statamic;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Statamic\Eloquent\Structures\NavTreeRepository;
use Statamic\Facades\Site;
use Statamic\Fieldtypes\Link\ArrayableLink;
use Statamic\Statamic;

class RapidezStatamic
{
    public function nav(string $key)
    {
        if (!str($key)->startsWith('nav:')) {
            return;
        }


        return Cache::rememberForever($key, fn() => $this->buildMenu($key));
    }

    protected function buildMenu(string $key)
    {
        $nav = Statamic::tag($key)->fetch();

        return $this->buildTree($nav);
    }

    protected function buildTree(array $elements, $parentId = null)
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['parent'] == $parentId) {
                $children = $this->buildTree($element['children'], $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
            }

            $element['url'] = $this->getUrl($element);
            $branch[] = $element;
        }

        return $branch;
    }

    private function getUrl(array $item): ?string
    {
        if ($item['category'] ?? false) {
            $urlPath = is_array($item['category']) ? ($item['category']['url_path'] ?? '') : ($item['category']->value()['url_path'] ?? '');
            return Str::start($urlPath, '/');
        }

        return $item['url'] ?? '';
    }

    public function getButtonUrl(array $item): ?string
    {
        if (!($item instanceof ArrayableLink)) {
            return '';
        }

        if ($item?->url() ?? false) {
            return $item->url();
        }

        $itemData = $item->toArray() ?? [];

        if (($itemData['linked_category'] ?? false) && ($itemData['linked_category']?->value() ?? false)) {
            $urlPath = is_array($itemData['linked_category']?->value()) ? ($itemData['linked_category']?->value()['url_path'] ?? '') : ($itemData['linked_category']->value()['url_path'] ?? '');
            return Str::start($urlPath, '/');
        }

        return '';
    }
}
