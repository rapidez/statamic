<?php

namespace Rapidez\Statamic\Extend;

use Illuminate\Support\Facades\Cache;
use Statamic\Sites\Sites;

class SitesLinkedToMagentoStores extends Sites
{
    public function findByUrl($url)
    {
        if ($site = $this->findByMageRunCode(request()->server('MAGE_RUN_CODE'))) {
            return $site;
        }

        return Cache::store('array')->rememberForever('rapidez.statamic.findByUrl-'.$url, fn() => parent::findByUrl($url));
    }


    public function findByMageRunCode($code)
    {
        if (!$code) {
            return null;
        }
        return Cache::store('array')->rememberForever('rapidez.statamic.findByMageRunCode-'.$code, fn() => collect($this->sites)->get($code));
    }
}
