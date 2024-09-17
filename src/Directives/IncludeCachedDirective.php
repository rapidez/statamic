<?php

namespace Rapidez\Statamic\Directives;

use Statamic\StaticCaching\Cachers\Writer;
use Statamic\View\View;

class IncludeCachedDirective
{
    public static function handle(string $view, array $vars = [])
    {
        $namespace = str($view)->explode('::');
        $path = '';
    
        if ($namespace->count() > 1) {
            $path = $namespace->first() . '/';
        }

        $path .= str($namespace->last())->replace('.', '/');

        $path = config('statamic.static_caching.strategies.full.path') . '/' . $path . '.html';

        if (!file_exists($path)) {
            $view = (new View)
                ->template($view)
                ->with($vars)
                ->render();
            
            $writer = app(Writer::class);
            $writer->write($path, $view);
            
            return $view;
        }

        return readfile($path);
    }

    public static function bind(): void
    {
        app()->singleton(static::class);
    }
}