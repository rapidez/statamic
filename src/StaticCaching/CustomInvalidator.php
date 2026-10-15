<?php

namespace Rapidez\Statamic\StaticCaching;

use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Contracts\Globals\Variables;
use Statamic\Contracts\Structures\Nav;
use Statamic\Contracts\Structures\NavTree;
use Statamic\Entries\Entry;
use Statamic\Forms\Form;
use Statamic\StaticCaching\DefaultInvalidator;
use Statamic\Support\Str;

class CustomInvalidator extends DefaultInvalidator
{
    public function invalidate($item)
    {
        if (
            $item instanceof GlobalSet
            || $item instanceof Variables
            || $item instanceof Nav
            || $item instanceof NavTree
            || $item instanceof Form
            || $this->rules === 'all'
        ) {
            Cache::flush();
            return $this->cacher->flush();
        }

        $urls = [];

        if ($item instanceof Entry && $item->collectionHandle() === 'categories' && $item->linked_category) {
            $urls[] = Str::ensureLeft($item->linked_category['url'], '/');
        }

        if (count($urls) >= 1) {
            $this->cacher->invalidateUrls($urls);
            return;
        }

        parent::invalidate($item);
    }
}
