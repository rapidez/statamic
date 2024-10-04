<?php

namespace Rapidez\Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rapidez\Statamic\StatamicNav
 */
class StatamicNav extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rapidez-statamic-nav';
    }
}