<?php

namespace Rapidez\Statamic\Tags;

use Statamic\Facades\Config;
use Statamic\Tags\Tags;

class Alternates extends Tags
{
    public function index()
    {
        $page = $this->params->get('page');
        $isHome = request()->path() == '/';

        return collect(Config::getOtherLocales($page->locale))
            ->filter(fn ($locale) => $isHome || ($page->collection->sites()->contains($locale) && $page->in($locale)?->status() === 'published'))
            ->filter(function ($locale) use ($page) {
                $currentPageSiteGroup = Config::getSite($page->locale)->attributes()['group'] ?? false;
                $otherLocaleSiteGroup = Config::getSite($locale)->attributes()['group'] ?? false;

                return $currentPageSiteGroup === $otherLocaleSiteGroup;
            })
            ->mapWithKeys(function ($site) use ($page, $isHome) {
                $lang = str(Config::getSite($site)->locale())->replace('_', '-')->lower()->value();
                $url = $isHome
                    ? Config::getSite($site)->absoluteUrl()
                    : $page->in($site)->absoluteUrl();

                return [$lang => $url];
            });
    }
}
