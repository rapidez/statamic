<?php

namespace Rapidez\Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @see \Rapidez\Statamic\RapidezStatamic
 */
class RapidezStatamic extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rapidez-statamic';
    }
}