<?php

namespace Rapidez\Statamic\Extend;

use Illuminate\Support\Collection;
use Statamic\Sites\Sites;

class SitesLinkedToMagentoStores extends Sites
{
    public function findByUrl($url)
    {
        if ($site = once(fn() => $this->findByMageRunCode(request()->server('MAGE_RUN_CODE')))) {
            return $site;
        }

        return once(fn() => parent::findByUrl($url));
    }


    public function findByMageRunCode($code)
    {
        if (!$code || !($this->sites instanceof Collection)) {
            return null;
        }
        return $this->sites->get($code));
    }
}
