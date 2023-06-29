<?php

namespace Rapidez\Statamic\Extend;

use Statamic\Sites\Sites;

class SitesLinkedToMagentoStores extends Sites
{
    public function findByMageRunCode($code)
    {
        return collect($this->sites)->get($code);
    }

    public function current()
    {
        return $this->current
            ?? $this->findByMageRunCode(request()->server('MAGE_RUN_CODE'))
            ?? $this->findByUrl(request()->getUri())
            ?? $this->default();
    }
}
