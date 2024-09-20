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

    protected function getUrl(array $item): ?string
    {
        if ($item['category'] ?? false) {
            $urlPath = is_array($item['category']) ? ($item['category']['url_path'] ?? '') : ($item['category']->value()['url_path'] ?? '');
            return Str::start($urlPath, '/');
        }

        return $item['url'] ?? '';
    }
}
