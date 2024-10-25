<?php

namespace Rapidez\Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\FrontendController;

class StatamicRewriteController extends Controller
{
    protected Controller $controller;

    public function __construct()
    {
        $this->middleware('statamic.web');

        $this->controller = new FrontendController;
    }

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
