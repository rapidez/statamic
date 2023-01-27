<?php

namespace Rapidez\Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Illuminate\Support\Facades\View;
use Statamic\Exceptions\NotFoundHttpException;

class StatamicRewriteController
{
    public function __invoke(Request $request)
    {
        $storeCode = config()->get('rapidez.store_code');

        $entry = Entry::query()
            ->where('collection', 'pages')
            ->where('locale', $storeCode)
            ->where('slug', $request->path() == '/' ? 'home' : $request->path())
            ->first();

        if (!$entry) {
            throw new NotFoundHttpException();
        }

        $template = View::exists($entry->template()) ? $entry->template() : $entry->blueprint->handle();

        echo view($template, ['entry' => $entry]);
    }
}
