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
            ->filter(fn ($locale) => $page->isRoot() || $page->in($locale)?->status() === 'published')
            ->filter(function ($locale) use ($page) {
                $currentPageSiteGroup = Config::getSite($page->locale)->attributes()['group'] ?? false;
                $otherLocaleSiteGroup = Config::getSite($locale)->attributes()['group'] ?? false;

                return $currentPageSiteGroup === $otherLocaleSiteGroup;
            })
            ->mapWithKeys(function ($site) use ($page) {
                $lang = str(Config::getSite($site)->locale())->replace('_', '-')->lower()->value();
                $url = $page->isRoot()
                    ? Config::getSite($site)->absoluteUrl()
                    : $page->in($site)->absoluteUrl();

                return [$lang => $url];
            });
    }
}
