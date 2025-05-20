<?php

namespace Rapidez\Statamic\Http\ViewComposers;

use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class ConfigComposer
{
    public function compose(View $view)
    {
        Config::set('frontend.statamic.translations', __('rapidez-statamic::frontend'));
    }
}
