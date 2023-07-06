<?php

namespace Rapidez\Statamic\Tags;

use Statamic\Facades\Config;
use Statamic\Tags\Tags;

class Alternates extends Tags
{
    public function index()
    {
        $page = $this->params->get('page');

        return collect(Config::getOtherLocales($page->locale))
            ->filter(fn ($locale) => $page->in($locale)?->status() === 'published')
            ->mapWithKeys(function ($site) use ($page) {
                $lang = str(Config::getSite($site)->locale())->replace('_', '-')->lower()->value();
                $url = $page->in($site)->absoluteUrl();
                return [$lang => $url];
            });
    }
}
