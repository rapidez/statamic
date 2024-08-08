<?php

namespace Rapidez\Statamic\Extend;

use Statamic\Sites\Site;
use Statamic\Sites\Sites;
use Illuminate\Support\Collection;

class SitesLinkedToMagentoStores extends Sites
{
    /**
     * @param string $url
     * @return Site
     */
    public function findByUrl($url): Site
    {
        if ($site = $this->findByMageRunCode((string) request()->server('MAGE_RUN_CODE'))) {
            return $site;
        }

        return parent::findByUrl($url);
    }


    public function findByMageRunCode(string $code): Site|null
    {
        $sites = collect($this->sites);

        return $sites->get($code);
    }
}
