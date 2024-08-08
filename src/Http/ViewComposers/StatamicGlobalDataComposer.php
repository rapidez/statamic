<?php

namespace Rapidez\Statamic\Http\ViewComposers;

use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Statamic\Eloquent\Globals\Variables;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;

class StatamicGlobalDataComposer
{
    /**
     * @return array<string, Variables>
     */
    public function getGlobals(): array
    {
        return Cache::rememberForever('statamic-globals-'.Site::current()->handle(), function() {
            $data = [];
            
            foreach (GlobalSet::all() as $set) {
                foreach ($set->localizations() as $locale => $variables) {
                    if ($locale == Site::current()->handle()) {
                        $data[$set->handle()] = $variables;
                    }
                }
            }
            
            return $data;
        });
    }

    public function compose(View $view) : View
    {
        if(!isset($view->globals)) {
            $view->with('globals', optionalDeep((object)$this->getGlobals()));
        }

        return $view;
    }
}
