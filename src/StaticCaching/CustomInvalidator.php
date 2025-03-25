<?php

namespace Rapidez\Statamic\StaticCaching;

use Statamic\Eloquent\Globals\GlobalSet;
use Statamic\Eloquent\Structures\Nav;
use Statamic\Entries\Entry;
use Statamic\StaticCaching\DefaultInvalidator;
use Statamic\Support\Str;

class CustomInvalidator extends DefaultInvalidator
{
    public function invalidate($item)
    {
        if ($item instanceof GlobalSet || $item instanceof Nav || $this->rules === 'all') {
            return $this->cacher->flush();
        }

        $urls = [];

        if ($item instanceof Entry && $item->collectionHandle() === 'categories' && $item->linked_category) {
            $urls[] = Str::ensureLeft($item->linked_category['url_path'], '/');
        }

        if (count($urls) >= 1) {
            $this->cacher->invalidateUrls($urls);
            return;
        }

        parent::invalidate($item);
    }
}
