<?php

namespace Rapidez\Statamic\Directives;

use Statamic\View\View;

class IncludeCachedDirective
{
    public static function handle(string $view, array $vars = []): View
    {
        return (new View)
            ->template($view)
            ->with($vars);

    }

    public static function bind(): void
    {
        app()->singleton(static::class);
    }
}