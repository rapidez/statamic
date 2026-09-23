<?php

namespace Rapidez\Statamic\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Rapidez\Statamic\Support\IndexerFilters;
use Statamic\Facades\User;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;

class Indexer extends Widget
{
    public function component(): ?VueComponent
    {
        if (! User::current()?->can('run rapidez indexer')) {
            return null;
        }

        $labels = __('rapidez-statamic::messages.indexer');

        return VueComponent::render('rapidez-indexer-widget', [
            'title' => $labels['title'],
            'runUrl' => cp_route('rapidez.indexer.run'),
            'lastIndexedAt' => $this->lastIndexedAt(),
            'typeOptions' => IndexerFilters::typeOptions(),
            'storeOptions' => IndexerFilters::storeOptions(),
            'labels' => $labels,
        ]);
    }

    protected function lastIndexedAt(): ?string
    {
        if (! Storage::disk('local')->exists('/.last-index')) {
            return null;
        }

        try {
            return Carbon::parse(Storage::disk('local')->get('/.last-index'))
                ->timezone(config('app.timezone'))
                ->toIso8601String();
        } catch (\Throwable) {
            return null;
        }
    }
}
