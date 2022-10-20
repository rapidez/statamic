<?php

namespace Rapidez\Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Illuminate\Support\Facades\View;

class StatamicRewriteController
{
    public function __invoke(Request $request)
    {
        $entry = Entry::query()
            ->where('collection', 'pages')
            ->where('slug', $request->path() == '/' ? 'home' : $request->path())
            ->first();

        $template = View::exists($entry->get('template')) ? $entry->get('template') : $entry->blueprint->handle();

        echo view($template, ['entry' => $entry]);
    }
}
