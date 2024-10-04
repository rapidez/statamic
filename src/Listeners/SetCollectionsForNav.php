<?php

namespace Rapidez\Statamic\Listeners;

use Statamic\Events\NavCreated;
use Statamic\Structures\Nav;

class SetCollectionsForNav
{
    public function handle(NavCreated $event): void
    {
        /** @var Nav $nav */
        $nav = $event->nav;
        
        $nav
            ->collections(config('rapidez.statamic.nav.allowed_collections'))
            ->save();
    }
}