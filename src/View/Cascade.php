<?php

namespace Rapidez\Statamic\View;

use Statamic\View\Cascade as StatamicCascade;

class Cascade extends StatamicCascade
{
    protected function hydrateGlobals()
    {
        return $this;
    }
}