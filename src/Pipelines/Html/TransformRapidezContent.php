<?php

namespace Rapidez\Statamic\Pipelines\Html;

use Closure;
use Rapidez\Core\Facades\Rapidez;

class TransformRapidezContent
{
    public function handle(string $html, Closure $next): string
    {
        return $next(Rapidez::content($html));
    }
}
