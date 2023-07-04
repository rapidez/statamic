<?php

namespace Rapidez\Statamic\Extend;

use Statamic\Sites\Sites;

class SitesLinkedToMagentoStores extends Sites
{
    public function findByUrl($url)
    {
        if ($site = $this->findByMageRunCode(request()->server('MAGE_RUN_CODE'))) {
            return $site;
        }

        return parent::findByUrl($url);
    }


    public function findByMageRunCode($code)
    {
        return collect($this->sites)->get($code);
    }
}
