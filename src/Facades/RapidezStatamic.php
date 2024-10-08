<?php

namespace Rapidez\Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Rapidez\Statamic\RapidezStatamic as RapidezStatamicFacade;

/**
 * @see \Rapidez\Statamic\RapidezStatamic
 */
class RapidezStatamic extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RapidezStatamicFacade::class;
    }
}