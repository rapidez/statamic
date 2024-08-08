<?php

namespace Rapidez\Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\FrontendController;

class StatamicRewriteController
{
    public function __invoke(Request $request): string
    {
        return (new FrontendController)->index($request);
    }
}
