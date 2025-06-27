<?php

namespace Rapidez\Statamic\Facades;

use Rapidez\Statamic\RapidezStatamic as RapidezStatamicFacade;
use Illuminate\Support\Facades\Facade;

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